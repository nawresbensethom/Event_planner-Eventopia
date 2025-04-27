<?php

namespace App\Controller\Client;

use App\Entity\Tache;
use App\Entity\Plan;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
#[Route('/tache')]
final class TacheController extends AbstractController
{
    #[Route('/plan/{id}', name: 'app_tache_by_plan', methods: ['GET'])]
    public function indexByPlan(Request $request, Plan $plan, TacheRepository $tacheRepository): Response
    {
        $taches = $tacheRepository->createQueryBuilder('t')
            ->where('t.plan = :plan')
            ->setParameter('plan', $plan)
            ->getQuery()
            ->getResult();

        return $this->render('frontoffice/tache/index.html.twig', [
            'taches' => $taches,
            'plan' => $plan,
        ]);
    }
    #[Route('/{id}/status', name: 'app_tache_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Tache $tache, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['statut'] ?? null;

        $allowed = ['En cours', 'Terminée', 'Annulée'];
        if (!in_array($newStatus, $allowed, true)) {
            return $this->json(
                ['success' => false, 'message' => 'Statut invalide'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $tache->setStatut($newStatus);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/new/plan/{id}', name: 'app_tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Plan $plan, EntityManagerInterface $entityManager): Response
    {
        $tache = new Tache();
        $tache->setPlan($plan);
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($tache);
                $entityManager->flush();

                $this->addFlash('success', 'La tâche a été créée avec succès.');
                return $this->redirectToRoute('app_tache_by_plan', ['id' => $plan->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la création de la tâche : ' . $e->getMessage());
            }
        }

        return $this->render('frontoffice/tache/new.html.twig', [
            'tache' => $tache,
            'form' => $form,
            'plan' => $plan,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
        return $this->render('frontoffice/tache/show.html.twig', [
            'tache' => $tache,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', 'La tâche a été modifiée avec succès.');
                return $this->redirectToRoute('app_tache_by_plan', ['id' => $tache->getPlan()->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification de la tâche : ' . $e->getMessage());
            }
        }

        return $this->render('frontoffice/tache/edit.html.twig', [
            'tache' => $tache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $planId = $tache->getPlan()->getId();
        if ($this->isCsrfTokenValid('delete' . $tache->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($tache);
                $entityManager->flush();

                $this->addFlash('success', 'La tâche a été supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression de la tâche : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_tache_by_plan', ['id' => $planId], Response::HTTP_SEE_OTHER);
    }
}
