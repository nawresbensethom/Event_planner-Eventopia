<?php

namespace App\Controller\Client;

use App\Entity\Plan;
use App\Entity\Tache;
use App\Form\PlanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plan')]
final class PlanController extends AbstractController
{
    #[Route('/', name: 'app_plan_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $plans = $entityManager->getRepository(Plan::class)->findAll();

        return $this->render('frontoffice/plan/index.html.twig', [
            'plans' => $plans,
        ]);
    }

    #[Route('/new', name: 'app_plan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plan = new Plan();
        $form = $this->createForm(PlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $entityManager->persist($plan);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le plan a été créé avec succès.');
                    return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la création du plan : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier les champs.');
            }
        }

        return $this->render('frontoffice/plan/new.html.twig', [
            'plan' => $plan,
            'form' => $form,
        ]);
    }
    #[Route('/dashboard', name: 'app_plan_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        try {
            $plans = $entityManager->getRepository(Plan::class)->findAll();
            $taches = $entityManager->getRepository(Tache::class)->findAll();

            // Statistiques des plans par priorité
            $plansByPriority = [
                'FAIBLE' => 0,
                'MOYENNE' => 0,
                'HAUTE' => 0,
            ];
            foreach ($plans as $plan) {
                $priorite = $plan->getPriorite();
                if ($priorite !== null && array_key_exists($priorite, $plansByPriority)) {
                    $plansByPriority[$priorite]++;
                }
            }

            // Statistiques des tâches par statut
            $tachesByStatus = [
                'Annulée' => 0,
                'En cours' => 0,
                'Terminée' => 0,
            ];
            foreach ($taches as $tache) {
                $statut = $tache->getStatut();
                if (array_key_exists($statut, $tachesByStatus)) {
                    $tachesByStatus[$statut]++;
                }
            }

            

            return $this->render('frontoffice/plan/dashboard.html.twig', [
                'plans' => $plans,
                'taches' => $taches,
                'plansByPriority' => $plansByPriority,
                'tachesByStatus' => $tachesByStatus,
                
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors du chargement du tableau de bord : ' . $e->getMessage());
            return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/{id}', name: 'app_plan_show', methods: ['GET'])]
    public function show(?Plan $plan): Response
    {
        if (!$plan) {
            $this->addFlash('error', 'Le plan demandé n\'existe pas.');
            return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/plan/show.html.twig', [
            'plan' => $plan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Plan $plan, EntityManagerInterface $entityManager): Response
    {
        if (!$plan) {
            $this->addFlash('error', 'Le plan demandé n\'existe pas.');
            return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', 'Le plan a été modifié avec succès.');
                return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification du plan : ' . $e->getMessage());
            }
        }

        return $this->render('frontoffice/plan/edit.html.twig', [
            'plan' => $plan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plan_delete', methods: ['POST'])]
    public function delete(Request $request, ?Plan $plan, EntityManagerInterface $entityManager): Response
    {
        if (!$plan) {
            $this->addFlash('error', 'Le plan demandé n\'existe pas.');
            return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $plan->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($plan);
                $entityManager->flush();

                $this->addFlash('success', 'Le plan et les tâches associées ont été supprimés avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du plan : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_plan_index', [], Response::HTTP_SEE_OTHER);
    }

    
}