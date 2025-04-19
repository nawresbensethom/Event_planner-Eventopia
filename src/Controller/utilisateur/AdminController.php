<?php

namespace App\Controller\utilisateur;

use App\Entity\Utilisateur;
use App\Entity\Profil;
use App\Repository\UtilisateurRepository;
use App\Repository\ProfilRepository;
use App\Form\UtilisateurType;
use App\Form\ProfilType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(UtilisateurRepository $utilisateurRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $organisateurs = $utilisateurRepo->findByRole('ROLE_ORGANISATEUR');
        $prestataires = $utilisateurRepo->findByRole('ROLE_PRESTATAIRE');

        return $this->render('backoffice/admin/dashboard.html.twig', [
            'organisateurs' => $organisateurs,
            'prestataires' => $prestataires,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(Request $request, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->doctrine->getRepository(Utilisateur::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            try {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('success', 'User deleted successfully');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting user: '.$e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/block/{id}', name: 'admin_block_user')]
    public function blockUser($id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->doctrine->getRepository(Utilisateur::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('admin_dashboard');
        }

        try {
            $user->setStatut('inactive');
            $entityManager = $this->doctrine->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'User blocked successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error blocking user: '.$e->getMessage());
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/unblock/{id}', name: 'admin_unblock_user')]
    public function unblockUser($id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->doctrine->getRepository(Utilisateur::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('admin_dashboard');
        }

        try {
            $user->setStatut('active');
            $entityManager = $this->doctrine->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'User unblocked successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error unblocking user: '.$e->getMessage());
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // 🔹 Gestion des profils
    #[Route('/admin/profils', name: 'admin_profils_index', methods: ['GET'])]
    public function profilsIndex(ProfilRepository $profilRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('backoffice/profil/index.html.twig', [
            'profils' => $profilRepository->findAll()
        ]);
    }

    #[Route('/admin/profil/{id}', name: 'admin_profil_show', methods: ['GET'])]
    public function showProfil(Profil $profil): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('backoffice/profil/show.html.twig', [
            'profil' => $profil
        ]);
    }

    #[Route('/admin/profil/new', name: 'admin_profil_new', methods: ['GET', 'POST'])]
    public function newProfil(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $profil = new Profil();
        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $newFilename = uniqid().'.'.$photoFile->guessExtension();
                    try {
                        $photoFile->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                        $profil->setPhoto($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo');
                    }
                }

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($profil);
                $entityManager->flush();

                $this->addFlash('success', 'Le profil a été créé avec succès');
                return $this->redirectToRoute('admin_profils_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du profil : ' . $e->getMessage());
            }
        }

        return $this->render('backoffice/profil/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/profil/{id}/edit', name: 'admin_profil_edit', methods: ['GET', 'POST'])]
    public function editProfil(Request $request, Profil $profil): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();
            $this->addFlash('success', 'Profil modifié avec succès');
            return $this->redirectToRoute('admin_profils_index');
        }

        return $this->render('backoffice/profil/edit.html.twig', [
            'profil' => $profil,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/profil/{id}/delete', name: 'admin_profil_delete', methods: ['POST'])]
    public function deleteProfil(Request $request, Profil $profil): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$profil) {
            $this->addFlash('error', 'Profil non trouvé');
            return $this->redirectToRoute('admin_profils_index');
        }

        if ($this->isCsrfTokenValid('delete' . $profil->getId(), $request->request->get('_token'))) {
            try {
                $entityManager = $this->doctrine->getManager();
                
                // Supprimer la photo associée si elle existe
                if ($profil->getPhoto()) {
                    $photoPath = $this->getParameter('photos_directory') . '/' . $profil->getPhoto();
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
                
                $entityManager->remove($profil);
                $entityManager->flush();
                
                $this->addFlash('success', 'Le profil a été supprimé avec succès');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du profil : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('admin_profils_index');
    }

    #[Route('/admin/profil/{id}/details', name: 'admin_profil_details', methods: ['GET'])]
    public function showUserProfil(Profil $profil): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/profil/show.html.twig', [
            'profil' => $profil,
            'user' => $profil->getUser()
        ]);
    }
}