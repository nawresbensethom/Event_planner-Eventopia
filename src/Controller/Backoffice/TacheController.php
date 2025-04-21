<?php

namespace App\Controller\Backoffice;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/tache')]
class TacheController extends AbstractController
{
    #[Route('/', name: 'backoffice_tache_index', methods: ['GET'])]
    public function index(TacheRepository $repo): Response
    {
        $taches = $repo->findAll();
        return $this->render('backoffice/tache/index.html.twig', [
            'taches' => $taches,
        ]);
    }

    #[Route('/new', name: 'backoffice_tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tache);
            $em->flush();
            $this->addFlash('success', 'Tâche créée avec succès.');
            return $this->redirectToRoute('backoffice_tache_index');
        }

        return $this->render('backoffice/tache/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
        return $this->render('backoffice/tache/show.html.twig', [
            'tache' => $tache,
        ]);
    }

    #[Route('/{id}/edit', name: 'backoffice_tache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Tâche modifiée avec succès.');
            return $this->redirectToRoute('backoffice_tache_index');
        }

        return $this->render('backoffice/tache/edit.html.twig', [
            'tache' => $tache,
            'form'  => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tache->getId(), $request->request->get('_token'))) {
            $em->remove($tache);
            $em->flush();
            $this->addFlash('success', 'Tâche supprimée avec succès.');
        }

        return $this->redirectToRoute('backoffice_tache_index');
    }
}
