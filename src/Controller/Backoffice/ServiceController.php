<?php

namespace App\Controller\Backoffice;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use App\Service\ExchangeRateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/backoffice/service')]
class ServiceController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_service_index', methods: ['GET'])]
    public function index(Request $request, ServiceRepository $serviceRepository, PaginatorInterface $paginator): Response
    {
        $query = $serviceRepository->createQueryBuilder('s')
            ->leftJoin('s.categorieService', 'c')
            ->addSelect('c')
            ->orderBy('s.id_service', 'DESC')
            ->getQuery();

        $services = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            3 // Nombre d'éléments par page
        );

        return $this->render('backoffice/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/search', name: 'app_backoffice_service_search', methods: ['GET'])]
    public function search(Request $request, ServiceRepository $serviceRepository): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('search', '');
            $minPrice = $request->query->get('minPrice');
            $maxPrice = $request->query->get('maxPrice');
            $category = $request->query->get('category');

            $queryBuilder = $serviceRepository->createQueryBuilder('s')
                ->leftJoin('s.categorieService', 'c')
                ->addSelect('c');

            if ($searchTerm) {
                $queryBuilder->andWhere('s.nom LIKE :searchTerm OR s.description LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            }

            if ($minPrice !== null && $minPrice !== '') {
                $queryBuilder->andWhere('s.tarif >= :minPrice')
                    ->setParameter('minPrice', $minPrice);
            }

            if ($maxPrice !== null && $maxPrice !== '') {
                $queryBuilder->andWhere('s.tarif <= :maxPrice')
                    ->setParameter('maxPrice', $maxPrice);
            }

            if ($category !== null && $category !== '') {
                $queryBuilder->andWhere('c.id = :category')
                    ->setParameter('category', $category);
            }

            $services = $queryBuilder->getQuery()->getResult();

            $results = [];
            foreach ($services as $service) {
                $results[] = [
                    'id' => $service->getIdService(),
                    'nom' => $service->getNom(),
                    'description' => $service->getDescription(),
                    'tarif' => $service->getTarif(),
                    'photos' => $service->getPhotos(),
                    'categorie' => $service->getCategorieService()->getNomCategorie(),
                    'token' => $this->generateToken($service),
                ];
            }

            return new JsonResponse($results);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function generateToken(Service $service): string
    {
        return $this->container->get('security.csrf.token_manager')
            ->getToken('delete' . $service->getIdService())
            ->getValue();
    }

    #[Route('/new', name: 'app_backoffice_service_new', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_backoffice_service_index');
        }

        return $this->render('backoffice/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('backoffice/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backoffice_service_edit', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_backoffice_service_index');
        }

        return $this->render('backoffice/service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getIdService(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_service_index');
    }

    #[Route('/api/convert', name: 'app_backoffice_service_convert', methods: ['GET'])]
    public function convertCurrency(Request $request, ExchangeRateService $exchangeRateService): JsonResponse
    {
        try {
            $amount = $request->query->get('amount', 1);
            $from = $request->query->get('from', 'EUR');
            $to = $request->query->get('to', 'USD');

            $result = $exchangeRateService->convert((float)$amount, $from, $to);
            $rates = $exchangeRateService->getExchangeRates($from);

            return new JsonResponse([
                'success' => true,
                'result' => $result,
                'rate' => $rates['rates'][$to] ?? 1
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 