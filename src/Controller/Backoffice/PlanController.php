<?php
namespace App\Controller\Backoffice;

use App\Entity\Plan;
use App\Form\PlanType;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/plan')]
class PlanController extends AbstractController
{
    #[Route('/', name: 'backoffice_plan_index', methods: ['GET'])]
    public function index(PlanRepository $repo): Response
    {
        return $this->render('backoffice/plan/index.html.twig', [
            'plans' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'backoffice_plan_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $plan = new Plan();
        $form = $this->createForm(PlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plan);
            $em->flush();
            $this->addFlash('success','Plan créé avec succès.');
            return $this->redirectToRoute('backoffice_plan_index');
        }

        return $this->render('backoffice/plan/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_plan_show', methods: ['GET'])]
    public function show(Plan $plan): Response
    {
        return $this->render('backoffice/plan/show.html.twig', [
            'plan' => $plan,
        ]);
    }

    #[Route('/{id}/edit', name: 'backoffice_plan_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Plan $plan, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success','Plan modifié avec succès.');
            return $this->redirectToRoute('backoffice_plan_index');
        }

        return $this->render('backoffice/plan/edit.html.twig', [
            'plan' => $plan,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_plan_delete', methods: ['POST'])]
    public function delete(Request $request, Plan $plan, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plan->getId(), $request->request->get('_token'))) {
            $em->remove($plan);
            $em->flush();
            $this->addFlash('success','Plan supprimé.');
        }

        return $this->redirectToRoute('backoffice_plan_index');
    }
}
