<?php

namespace App\Controller\Client;

use App\Entity\Offreemploi;
use App\Entity\Utilisateur;
use App\Form\OffreemploiType;
use App\Repository\OffreemploiRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/client/offreemploi')]
final class OffreemploiController extends AbstractController
{
    #[Route('/', name: 'client_offreemploi_index', methods: ['GET'])]
    public function index(OffreemploiRepository $offreemploiRepository): Response
    {
        return $this->render('frontoffice/offreemploi/index.html.twig', [
            'offreemplois' => $offreemploiRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'client_offreemploi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Offreemploi instance
        $offreemploi = new Offreemploi();

        // Set the 'id_user' to the user with id = 1 (from the database)
        $user = $entityManager->getRepository(Utilisateur::class)->find(1);

        if ($user) {
            // Set the user as the id_user
            $offreemploi->setIdUser($user);
        } else {
            // Handle the case where the user with id 1 doesn't exist
            // You might want to throw an exception or handle this case gracefully
            throw $this->createNotFoundException('User not found.');
        }

        // Create and handle the form
        $form = $this->createForm(OffreemploiType::class, $offreemploi);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new offer to the database
            $entityManager->persist($offreemploi);
            $entityManager->flush();

            // Redirect to the index page after saving the offer
            return $this->redirectToRoute('client_offreemploi_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form view if not submitted or not valid
        return $this->render('frontoffice/offreemploi/new.html.twig', [
            'offreemploi' => $offreemploi,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_offre}', name: 'client_offreemploi_show', methods: ['GET'])]
    public function show(Offreemploi $offreemploi): Response
    {
        return $this->render('frontoffice/offreemploi/show.html.twig', [
            'offreemploi' => $offreemploi,
        ]);
    }

    #[Route('/{id_offre}/edit', name: 'client_offreemploi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offreemploi $offreemploi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreemploiType::class, $offreemploi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('client_offreemploi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/offreemploi/edit.html.twig', [
            'offreemploi' => $offreemploi,
            'form' => $form,
        ]);
    }

    #[Route('/{id_offre}', name: 'client_offreemploi_delete', methods: ['POST'])]
    public function delete(Request $request, Offreemploi $offreemploi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offreemploi->getIdOffre(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offreemploi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('client_offreemploi_index', [], Response::HTTP_SEE_OTHER);
    }

  
}