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
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findBy([], ['date_publication' => 'DESC']);

        return $this->render('frontoffice/post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'app_client_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
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
                // Check if user exists
                $userId = $form->get('id_utilisateur')->getData();
                $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($userId);
                
                if (!$utilisateur) {
                    $this->addFlash('error', 'User ID ' . $userId . ' does not exist.');
                    return $this->render('frontoffice/post/new.html.twig', [
                        'post' => $post,
                        'form' => $form->createView(),
                    ]);
                }

                // Set post metadata
                $post->setDatePublication(new DateTime());
                $post->setIdUtilisateur($utilisateur);
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
                } else {
                    $post->setPhotos(null);
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
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if user exists
            $userId = $form->get('id_utilisateur')->getData();
            $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($userId);
            
            if (!$utilisateur) {
                $this->addFlash('error', 'User ID ' . $userId . ' does not exist.');
                return $this->render('frontoffice/post/edit.html.twig', [
                    'post' => $post,
                    'form' => $form->createView(),
                ]);
            }

            $post->setIdUtilisateur($utilisateur);

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
}
