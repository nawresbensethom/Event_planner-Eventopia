<?php

namespace App\Controller\utilisateur;

use App\Entity\Profil;
use App\Entity\Utilisateur;
use App\Form\ProfilType;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilController extends AbstractController
{
    private string $photoDir;

    public function __construct(string $photoDir = 'uploads/photos/profils')
    {
        $this->photoDir = $photoDir;
    }

    private function handlePhotoUpload($photoFile, SluggerInterface $slugger): string
    {
        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('kernel.project_dir').'/public/'.$this->photoDir,
                    $newFilename
                );
                return $this->photoDir.'/'.$newFilename;
            } catch (\Exception $e) {
                throw new \Exception('Une erreur est survenue lors de l\'upload de l\'image');
            }
        }
        return '';
    }

    // 🔹 Initialize profile with signup data
    private function createProfileFromUser(Utilisateur $user): Profil
    {
        $profil = new Profil();
        $profil->setUser($user)
               ->setDescription('')
               ->setRating(null)
               ->setPhoto(null);

        return $profil;
    }

    // 🔹 FRONT - View user profile
    #[Route('/profil', name: 'app_profil_show', methods: ['GET'])]
    public function show(ProfilRepository $profilRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $profil = $profilRepository->findOneBy(['user' => $user]);

        // Auto-create profile if doesn't exist with signup data
        if (!$profil) {
            $profil = $this->createProfileFromUser($user);
            $em->persist($profil);
            $em->flush();
        }

        return $this->render('frontoffice/profil/show.html.twig', [
            'profil' => $profil
        ]);
    }

    // 🔹 FRONT - Edit profile
    #[Route('/profil/edit', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, ProfilRepository $repo, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $profil = $repo->findOneBy(['user' => $user]) ?? $this->createProfileFromUser($user);
        $oldPhoto = $profil->getPhoto();

        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle photo removal if requested
            if ($form->has('remove_photo') && $form->get('remove_photo')->getData()) {
                if ($oldPhoto && file_exists($this->getParameter('kernel.project_dir').'/public/'.$oldPhoto)) {
                    unlink($this->getParameter('kernel.project_dir').'/public/'.$oldPhoto);
                    $profil->setPhoto(null);
                }
            }
            
            // Gestion de l'upload de la photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($oldPhoto && file_exists($this->getParameter('kernel.project_dir').'/public/'.$oldPhoto)) {
                    unlink($this->getParameter('kernel.project_dir').'/public/'.$oldPhoto);
                }
                
                $newPhotoPath = $this->handlePhotoUpload($photoFile, $slugger);
                $profil->setPhoto($newPhotoPath);
            }

            $em->persist($profil);
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour');
            return $this->redirectToRoute('app_profil_show');
        }

        return $this->render('frontoffice/profil/edit.html.twig', [
            'form' => $form->createView(),
            'profil' => $profil
        ]);
    }

    #[Route('/admin/profils', name: 'admin_profils_index')]
    public function adminIndex(ProfilRepository $profilRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/profil/index.html.twig', [
            'profils' => $profilRepository->findAll(),
        ]);
    }

    #[Route('/admin/profil/{id}', name: 'admin_profil_show')]
    public function adminShow(Profil $profil): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/profil/show.html.twig', [
            'profil' => $profil
        ]);
    }

    #[Route('/admin/profil/{id}/edit', name: 'admin_profil_edit', methods: ['GET', 'POST'])]
    public function adminEdit(Request $request, Profil $profil, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil modifié avec succès');
            return $this->redirectToRoute('admin_profils_index');
        }

        return $this->render('backoffice/profil/edit.html.twig', [
            'profil' => $profil,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/profil/{id}/delete', name: 'admin_profil_delete', methods: ['POST'])]
    public function adminDelete(Request $request, Profil $profil, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$profil->getId(), $request->request->get('_token'))) {
            $entityManager->remove($profil);
            $entityManager->flush();
            $this->addFlash('success', 'Profil supprimé avec succès');
        }

        return $this->redirectToRoute('admin_profils_index');
    }
}
