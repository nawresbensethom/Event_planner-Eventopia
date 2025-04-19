<?php

namespace App\Controller\Backoffice;

use App\Entity\SignalementPost;
use App\Form\SignalementPostType;
use App\Form\SignalementPostAdminType;
use App\Repository\SignalementPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backoffice/signalement/post')]
class SignalementPostController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_signalement_post_index', methods: ['GET'])]
    public function index(SignalementPostRepository $signalementPostRepository): Response
    {
        return $this->render('backoffice/signalement_post/index.html.twig', [
            'signalement_posts' => $signalementPostRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backoffice_signalement_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $signalementPost = new SignalementPost();
        $form = $this->createForm(SignalementPostType::class, $signalementPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signalementPost);
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_signalement_post_index');
        }

        return $this->render('backoffice/signalement_post/new.html.twig', [
            'signalement_post' => $signalementPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_signalement_post_show', methods: ['GET'])]
    public function show(SignalementPost $signalementPost): Response
    {
        return $this->render('backoffice/signalement_post/show.html.twig', [
            'signalement_post' => $signalementPost,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backoffice_signalement_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignalementPostType::class, $signalementPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_signalement_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/signalement_post/edit.html.twig', [
            'signalement_post' => $signalementPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_signalement_post_delete', methods: ['POST'])]
    public function delete(Request $request, SignalementPost $signalementPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signalementPost->getIdSignalementPost(), $request->request->get('_token'))) {
            $entityManager->remove($signalementPost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_signalement_post_index', [], Response::HTTP_SEE_OTHER);
    }
} 