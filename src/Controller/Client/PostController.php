<?php

namespace App\Controller\Client;

use App\Entity\Post;
use App\Entity\Utilisateur;
use App\Form\PostType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

#[Route('/frontoffice/post')]
final class PostController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_client_post_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $postsPerPage = 6;
        $currentPage = $request->query->getInt('page', 1);
        
        $postRepository = $entityManager->getRepository(Post::class);
        $totalPosts = $postRepository->count([]);
        $totalPages = ceil($totalPosts / $postsPerPage);
        
        $posts = $postRepository->findBy(
            [],
            ['date_publication' => 'DESC'],
            $postsPerPage,
            ($currentPage - 1) * $postsPerPage
        );

        return $this->render('frontoffice/post/index.html.twig', [
            'posts' => $posts,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'postsPerPage' => $postsPerPage,
            'totalPosts' => $totalPosts,
        ]);
    }

    #[Route('/new', name: 'app_client_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You need to be logged in to create a post.');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                $this->logger->error('Form validation errors: ' . json_encode($form->getErrors(true)));
            } else {
                // Set post metadata
                $post->setDatePublication(new DateTime());
                $post->setIdUtilisateur($user);
                $post->setStatut('Published');

                // Handle photo uploads
                $photos = $form->get('photos')->getData();
                $photoFilenames = [];
                $uploadDir = $this->getParameter('photos_directory');
                
                // Ensure upload directory exists
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $this->logger->info('Upload directory: ' . $uploadDir);
                
                if ($photos) {
                    foreach ($photos as $photo) {
                        try {
                            if (!$photo->isValid()) {
                                $this->addFlash('error', 'Invalid file: ' . $photo->getClientOriginalName());
                                $this->logger->error('Invalid file: ' . $photo->getClientOriginalName());
                                continue;
                            }

                            // Generate a unique filename
                            $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();
                            $targetPath = $uploadDir . '/' . $newFilename;

                            $this->logger->info('Attempting to move file to: ' . $targetPath);

                            // Move the file to the directory
                            $photo->move($uploadDir, $newFilename);
                            $photoFilenames[] = $newFilename;
                            
                            $this->logger->info('Successfully uploaded: ' . $newFilename);
                        } catch (FileException $e) {
                            $this->logger->error('File upload error: ' . $e->getMessage());
                            $this->addFlash('error', 'An error occurred while uploading the photo: ' . $photo->getClientOriginalName());
                            continue;
                        }
                    }
                }

                // Store filenames in the database
                if (!empty($photoFilenames)) {
                    $jsonPhotos = json_encode($photoFilenames);
                    $this->logger->info('Storing photos in database: ' . $jsonPhotos);
                    $post->setPhotos($jsonPhotos);
                }

                try {
                    $entityManager->persist($post);
                    $entityManager->flush();
                    $this->addFlash('success', 'Your post has been created successfully!');
                    return $this->redirectToRoute('app_client_post_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    $this->logger->error('Database error: ' . $e->getMessage());
                    
                    // If database operation fails, clean up uploaded files
                    foreach ($photoFilenames as $filename) {
                        $filePath = $uploadDir . '/' . $filename;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    $this->addFlash('error', 'An error occurred while saving your post. Please try again.');
                }
            }
        }

        return $this->render('frontoffice/post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_post}', name: 'app_client_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('frontoffice/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id_post}/edit', name: 'app_client_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Check if user is logged in and is the owner of the post
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You need to be logged in to edit a post.');
        }

        if ($post->getIdUtilisateur() !== $user) {
            throw new AccessDeniedException('You can only edit your own posts.');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle photo uploads
            $photos = $form->get('photos')->getData();
            $uploadDir = $this->getParameter('photos_directory');
            
            if ($photos) {
                // Delete old photos if they exist
                if ($post->getPhotos()) {
                    $oldPhotos = json_decode($post->getPhotos(), true) ?? [];
                    foreach ($oldPhotos as $oldPhoto) {
                        $oldPhotoPath = $uploadDir . '/' . $oldPhoto;
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                }
                
                // Upload new photos
                $photoFilenames = [];
                foreach ($photos as $photo) {
                    try {
                        $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                        $photo->move($uploadDir, $newFilename);
                        $photoFilenames[] = $newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'An error occurred while uploading the photo: ' . $photo->getClientOriginalName());
                        continue;
                    }
                }
                
                // Update photos in database
                $post->setPhotos(!empty($photoFilenames) ? json_encode($photoFilenames) : null);
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Your post has been updated successfully!');
                return $this->redirectToRoute('app_client_post_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating your post. Please try again.');
            }
        }

        return $this->render('frontoffice/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_post}/delete', name: 'app_client_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getIdPost(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($post);
                $entityManager->flush();
                $this->addFlash('success', 'La publication a été supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la publication.');
                return $this->redirectToRoute('app_client_post_show', ['id_post' => $post->getIdPost()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_client_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id_post}/like', name: 'app_client_post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($post->getDislikeCount() > 0) {
                $post->switchToLike();
            } else {
                $post->toggleLike();
            }
            
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'likes' => $post->getLikeCount(),
                'dislikes' => $post->getDislikeCount()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in like action: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/{id_post}/dislike', name: 'app_client_post_dislike', methods: ['POST'])]
    public function dislike(Post $post, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($post->getLikeCount() > 0) {
                $post->switchToDislike();
            } else {
                $post->toggleDislike();
            }
            
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'likes' => $post->getLikeCount(),
                'dislikes' => $post->getDislikeCount()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in dislike action: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }
}
