<?php

namespace App\Controller\Backoffice;

use App\Entity\Code_promos;
use App\Form\CodePromosType;
use App\Repository\Code_promosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Attribute\MapEntity;

#[Route('/backoffice/code-promos')]
class CodePromosController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_code_promos_index', methods: ['GET'])]
    public function index(Code_promosRepository $codePromosRepository): Response
    {
        return $this->render('backoffice/code_promos/index.html.twig', [
            'code_promos' => $codePromosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backoffice_code_promos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $codePromo = new Code_promos();
        $form = $this->createForm(CodePromosType::class, $codePromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($codePromo);
            $entityManager->flush();

            $this->addFlash('success', 'Code promotionnel créé avec succès.');
            return $this->redirectToRoute('app_backoffice_code_promos_index');
        }

        return $this->render('backoffice/code_promos/new.html.twig', [
            'code_promo' => $codePromo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_code_promos_show', methods: ['GET'])]
    public function show(#[MapEntity(class: Code_promos::class)] Code_promos $codePromo): Response
    {
        return $this->render('backoffice/code_promos/show.html.twig', [
            'code_promo' => $codePromo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backoffice_code_promos_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(class: Code_promos::class)] Code_promos $codePromo,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(CodePromosType::class, $codePromo);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            $this->addFlash('success', 'Code promotionnel modifié avec succès.');
            return $this->redirectToRoute('app_backoffice_code_promos_index');
        }
    
        return $this->render('backoffice/code_promos/edit.html.twig', [
            'code_promo' => $codePromo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_backoffice_code_promos_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(class: Code_promos::class)] Code_promos $codePromo,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$codePromo->getId(), $request->request->get('_token'))) {
            $entityManager->remove($codePromo);
            $entityManager->flush();

            $this->addFlash('success', 'Code promotionnel supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Échec de la suppression : jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_backoffice_code_promos_index');
    }
} 