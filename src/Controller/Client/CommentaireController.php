<?php

namespace App\Controller\Client;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Service\CommentValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/frontoffice/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/new/{id_post}', name: 'app_client_commentaire_new', methods: ['POST'])]
    public function new(
        Request $request, 
        Post $id_post, 
        EntityManagerInterface $entityManager,
        CommentValidator $commentValidator
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'error' => 'Vous devez être connecté pour commenter.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $contenu = $request->request->get('contenu');
        if (empty(trim($contenu))) {
            return new JsonResponse([
                'error' => 'Le commentaire ne peut pas être vide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation du contenu
        $motInterdit = $commentValidator->contientMotInterdit($contenu);
        if ($motInterdit !== null) {
            return new JsonResponse([
                'error' => sprintf('Le mot interdit "%s" a été détecté.', $motInterdit)
            ], Response::HTTP_BAD_REQUEST);
        }

        $commentaire = new Commentaire();
        $commentaire->setIdPost($id_post);
        $commentaire->setIdUtilisateur($user);
        $commentaire->setContenu($contenu);
        $commentaire->setDateCommentaire(new \DateTime());

        try {
            $entityManager->persist($commentaire);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Votre commentaire a été publié.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la publication du commentaire.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id_commentaire}/delete', name: 'app_client_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $commentaire->getIdCommentaire(), $token)) {
            return new JsonResponse([
                'error' => 'Token CSRF invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($this->getUser() !== $commentaire->getIdUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse([
                'error' => 'Vous n\'avez pas les droits pour supprimer ce commentaire.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $entityManager->remove($commentaire);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Commentaire supprimé avec succès.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer ce commentaire. Veuillez réessayer plus tard.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id_commentaire}/edit', name: 'app_client_commentaire_edit', methods: ['POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('edit' . $commentaire->getIdCommentaire(), $token)) {
            return new JsonResponse([
                'error' => 'Token CSRF invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($this->getUser() !== $commentaire->getIdUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse([
                'error' => 'Vous n\'avez pas les droits pour modifier ce commentaire.'
            ], Response::HTTP_FORBIDDEN);
        }

        $contenu = $request->request->get('contenu');
        if (empty(trim($contenu))) {
            return new JsonResponse([
                'error' => 'Le commentaire ne peut pas être vide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $commentaire->setContenu($contenu);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Commentaire modifié avec succès.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la modification du commentaire.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 