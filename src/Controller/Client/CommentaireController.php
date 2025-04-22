<?php

namespace App\Controller\Client;

use App\Entity\Commentaire;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/frontoffice/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/new/{id_post}', name: 'app_client_commentaire_new', methods: ['POST'])]
    public function new(Request $request, Post $id_post, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour commenter.');
            return $this->redirectToRoute('app_client_post_index');
        }

        $contenu = $request->request->get('contenu');
        if (empty(trim($contenu))) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('app_client_post_index');
        }

        $commentaire = new Commentaire();
        $commentaire->setIdPost($id_post);
        $commentaire->setIdUtilisateur($user);
        $commentaire->setContenu($contenu);
        $commentaire->setDateCommentaire(new \DateTime());

        try {
            $entityManager->persist($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire a été publié.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la publication du commentaire.');
        }

        return $this->redirectToRoute('app_client_post_index');
    }

    #[Route('/{id_commentaire}/delete', name: 'app_client_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getIdCommentaire(), $request->request->get('_token'))) {
            if ($this->getUser() === $commentaire->getIdUtilisateur() || $this->isGranted('ROLE_ADMIN')) {
                try {
                    $entityManager->remove($commentaire);
                    $entityManager->flush();
                    $this->addFlash('success', 'Le commentaire a été supprimé.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la suppression du commentaire.');
                }
            } else {
                $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
            }
        }

        return $this->redirectToRoute('app_client_post_index');
    }
} 