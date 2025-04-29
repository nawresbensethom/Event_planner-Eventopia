<?php

namespace App\Controller\Backoffice;

use App\Entity\Categorieservice;
use App\Form\CategorieserviceType;
use App\Repository\CategorieserviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/backoffice/categorieservice')]
class CategorieserviceController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_categorieservice_index', methods: ['GET'])]
    public function index(Request $request, CategorieserviceRepository $categorieserviceRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $categorieserviceRepository->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder, // Query
            $request->query->getInt('page', 1), // Page number
            3 // Limit per page
        );

        return $this->render('backoffice/categorieservice/index.html.twig', [
            'categorieservices' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_backoffice_categorieservice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieservice = new Categorieservice();
        $form = $this->createForm(CategorieserviceType::class, $categorieservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieservice);
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_categorieservice_index');
        }

        return $this->render('backoffice/categorieservice/new.html.twig', [
            'categorieservice' => $categorieservice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_categorieservice_show', methods: ['GET'])]
    public function show(Categorieservice $categorieservice): Response
    {
        return $this->render('backoffice/categorieservice/show.html.twig', [
            'categorieservice' => $categorieservice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backoffice_categorieservice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorieservice $categorieservice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieserviceType::class, $categorieservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_categorieservice_index');
        }

        return $this->render('backoffice/categorieservice/edit.html.twig', [
            'categorieservice' => $categorieservice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_categorieservice_delete', methods: ['POST'])]
    public function delete(Request $request, Categorieservice $categorieservice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieservice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorieservice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_categorieservice_index');
    }
} 