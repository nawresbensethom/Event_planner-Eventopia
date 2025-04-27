<?php

namespace App\Controller\Backoffice;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backoffice/adminblog')]
class AdminBlogController extends AbstractController
{
#[Route('/stats', name: 'admin_blog_stats', methods: ['GET'])]
public function stats(PostRepository $postRepository): Response
{
    return $this->render('backoffice/adminblog/posts/stats.html.twig', [
        'topLikes' => $postRepository->getTopUsersByLikes(),
        'topComments' => $postRepository->getTopUsersByComments()
    ]);
}
#[Route('/posts', name: 'admin_blog_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['date_publication' => 'DESC']);

        return $this->render('backoffice/adminblog/posts/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/posts/new', name: 'admin_blog_new_post', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Le post a été créé avec succès');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('backoffice/adminblog/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }
#[Route('/posts/{id_post}/change-status', name: 'admin_blog_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;
        $validStatuses = ['Published', 'Brouillon', 'En_attente', 'Rejected'];

        if (!$newStatus || !in_array($newStatus, $validStatuses)) {
            return $this->json(['error' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $post->setStatut($newStatus);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Statut du post mis à jour avec succès',
                'newStatus' => $newStatus
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Une erreur est survenue lors de la mise à jour du statut'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/posts/{id_post}/delete', name: 'admin_blog_delete_post', methods: ['POST'])]
    public function deletePost(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$post->getIdPost(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('admin_blog_index');
        }

        try {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Le post a été supprimé avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du post');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/posts/{id_post}', name: 'admin_blog_show_post', methods: ['GET'])]
    public function showPost(Post $post): Response
    {
        return $this->render('backoffice/adminblog/posts/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/posts/{id_post}/edit', name: 'admin_blog_edit_post', methods: ['GET', 'POST'])]
    public function editPost(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le post a été modifié avec succès');
                return $this->redirectToRoute('admin_blog_show_post', ['id_post' => $post->getIdPost()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du post');
            }
        }

        return $this->render('backoffice/adminblog/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }
} 