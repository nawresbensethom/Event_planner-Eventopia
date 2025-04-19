<?php

namespace App\Controller\Client;

use App\Entity\SignalementPost;
use App\Form\SignalementPostType;
use App\Form\SignalementPostNewType;
use App\Form\SignalementPostClientEditType;
use App\Repository\SignalementPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/frontoffice/signalement')]
final class SignalementPostController extends AbstractController
{
    #[Route('/post', name: 'app_client_signalement_post_index', methods: ['GET'])]
    public function index(SignalementPostRepository $signalementPostRepository): Response
    {
        return $this->render('frontoffice/signalement_post/index.html.twig', [
            'signalement_posts' => $signalementPostRepository->findAll(),
        ]);
    }

    #[Route('/post/new', name: 'app_client_signalement_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $signalementPost = new SignalementPost();
        $form = $this->createForm(SignalementPostNewType::class, $signalementPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($signalementPost);
                $entityManager->flush();
                $this->addFlash('success', 'Votre signalement a été envoyé avec succès.');
                return $this->redirectToRoute('app_client_signalement_post_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du signalement.');
            }
        }

        return $this->render('frontoffice/signalement_post/new.html.twig', [
            'signalement_post' => $signalementPost,
            'form' => $form,
        ]);
    }

    #[Route('/post/{id_signalement_post}', name: 'app_client_signalement_post_show', methods: ['GET'])]
    public function show(SignalementPost $signalementPost): Response
    {
        return $this->render('frontoffice/signalement_post/show.html.twig', [
            'signalement_post' => $signalementPost,
        ]);
    }

    #[Route('/post/{id_signalement_post}/edit', name: 'app_client_signalement_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignalementPostClientEditType::class, $signalementPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le signalement a été modifié avec succès.');
                return $this->redirectToRoute('app_client_signalement_post_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du signalement.');
            }
        }

        return $this->render('frontoffice/signalement_post/edit.html.twig', [
            'signalement_post' => $signalementPost,
            'form' => $form,
        ]);
    }

    #[Route('/post/{id_signalement_post}/delete', name: 'app_client_signalement_post_delete', methods: ['POST'])]
    public function delete(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signalementPost->getId_signalement_post(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($signalementPost);
                $entityManager->flush();
                $this->addFlash('success', 'Le signalement a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du signalement.');
            }
        }

        return $this->redirectToRoute('app_client_signalement_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/post/{id_signalement_post}/status', name: 'app_client_signalement_post_status', methods: ['POST'])]
    public function updateStatus(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        if (in_array($status, ['En attente', 'Traité'])) {
            $signalementPost->setStatut($status);
            $entityManager->flush();
            $this->addFlash('success', 'Le statut du signalement a été mis à jour.');
        }

        return $this->redirectToRoute('app_client_signalement_post_index');
    }

    // Routes Backend
    #[Route('/backoffice/adminblog/signalement/post', name: 'admin_signalement_post_index', methods: ['GET'])]
    public function adminIndex(SignalementPostRepository $signalementPostRepository): Response
    {
        return $this->render('backoffice/adminblog/signalements/index.html.twig', [
            'signalement_posts' => $signalementPostRepository->findAll(),
        ]);
    }

    #[Route('/backoffice/adminblog/signalement/post/{id_signalement_post}', name: 'admin_signalement_post_show', methods: ['GET'])]
    public function adminShow(SignalementPost $signalementPost): Response
    {
        return $this->render('backoffice/adminblog/signalements/show.html.twig', [
            'signalement_post' => $signalementPost,
        ]);
    }

    #[Route('/backoffice/adminblog/signalement/post/{id_signalement_post}/status', name: 'admin_signalement_post_status', methods: ['GET'])]
    public function toggleStatus(SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        // Basculer le statut
        $currentStatus = $signalementPost->getStatut();
        $newStatus = ($currentStatus === 'En attente') ? 'Traité' : 'En attente';
        $signalementPost->setStatut($newStatus);
        
        $entityManager->flush();
        
        return $this->redirectToRoute('admin_signalement_post_index');
    }

    #[Route('/backoffice/adminblog/signalement/post/{id_signalement_post}/delete', name: 'admin_signalement_post_delete', methods: ['POST'])]
    public function adminDelete(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signalementPost->getId_signalement_post(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($signalementPost);
                $entityManager->flush();
                $this->addFlash('success', 'Le signalement a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du signalement.');
            }
        }

        return $this->redirectToRoute('admin_signalement_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
