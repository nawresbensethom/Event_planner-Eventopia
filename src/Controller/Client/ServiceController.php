<?php

namespace App\Controller\Client;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/client/service')]
class ServiceController extends AbstractController
{
    #[Route('/', name: 'app_client_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('frontoffice/service/index.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/search', name: 'app_client_service_search', methods: ['GET', 'POST'])]
    public function search(Request $request, ServiceRepository $serviceRepository): JsonResponse
    {
        try {
            // Récupérer les paramètres de recherche selon la méthode HTTP
            if ($request->isMethod('POST')) {
                $data = json_decode($request->getContent(), true);
                $searchTerm = $data['search'] ?? '';
                $minPrice = $data['minPrice'] ?? null;
                $maxPrice = $data['maxPrice'] ?? null;
                $category = $data['category'] ?? null;
            } else {
                $searchTerm = $request->query->get('search', '');
                $minPrice = $request->query->get('minPrice');
                $maxPrice = $request->query->get('maxPrice');
                $category = $request->query->get('category');
            }

            $queryBuilder = $serviceRepository->createQueryBuilder('s')
                ->leftJoin('s.categorieService', 'c')
                ->addSelect('c');

            if (!empty($searchTerm)) {
                $queryBuilder->andWhere('s.nom LIKE :searchTerm OR s.description LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            }

            if (!empty($minPrice)) {
                $queryBuilder->andWhere('s.tarif >= :minPrice')
                    ->setParameter('minPrice', (float)$minPrice);
            }

            if (!empty($maxPrice)) {
                $queryBuilder->andWhere('s.tarif <= :maxPrice')
                    ->setParameter('maxPrice', (float)$maxPrice);
            }

            if (!empty($category)) {
                $queryBuilder->andWhere('c.id = :category')
                    ->setParameter('category', $category);
            }

            $services = $queryBuilder->getQuery()->getResult();

            $results = [];
            foreach ($services as $service) {
                $results[] = [
                    'idService' => $service->getIdService(),
                    'nom' => $service->getNom(),
                    'description' => $service->getDescription(),
                    'tarif' => $service->getTarif(),
                    'photos' => $service->getPhotos(),
                    'categorieService' => [
                        'id' => $service->getCategorieService()->getId(),
                        'nomCategorie' => $service->getCategorieService()->getNomCategorie()
                    ]
                ];
            }

            return new JsonResponse($results);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/new', name: 'app_client_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de fichier
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('services_directory'),
                        $newFilename
                    );
                    $service->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image');
                }
            }

            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_service_index');
        }

        return $this->render('frontoffice/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/favorites', name: 'app_client_service_favorites', methods: ['GET'])]
    public function favorites(ServiceRepository $serviceRepository): Response
    {
        return $this->render('frontoffice/service/favorites.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de fichier
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                // Suppression de l'ancienne photo si elle existe
                $oldFilename = $service->getPhotos();
                if ($oldFilename) {
                    $oldFilePath = $this->getParameter('services_directory').'/'.$oldFilename;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('services_directory'),
                        $newFilename
                    );
                    $service->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_client_service_index');
        }

        return $this->render('frontoffice/service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('frontoffice/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}', name: 'app_client_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getIdService(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_service_index');
    }
} 