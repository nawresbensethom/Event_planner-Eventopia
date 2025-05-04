<?php

namespace App\Controller\Client;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

#[Route('/reservationfront')]
final class ReservationControllerFront extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('frontoffice/reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'app_reservation_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        \App\Repository\EvenementRepository $evenementRepository,
        \App\Repository\ServiceRepository $serviceRepository,
        \Symfony\Component\Security\Core\Security $security
    ): JsonResponse {
        try {
            $this->logger->info('Starting reservation creation.');

            // Vérifier que l'utilisateur est connecté
            if (!$security->getUser()) {
                $this->logger->error('User not authenticated.');
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté pour effectuer une réservation.'], 401);
            }

            // Décoder les données JSON
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                $this->logger->error('Invalid JSON data.');
                return new JsonResponse(['success' => false, 'error' => 'Données JSON invalides.'], 400);
            }

            $this->logger->info('Received data:', $data);

            // Valider les données reçues
            $evenementId = $data['evenement_id'] ?? null;
            $serviceIds = $data['services'] ? array_filter(array_map('trim', explode(',', $data['services']))) : [];
            $promoCode = $data['promoCode'] ?? null;

            $this->logger->info('Input data', ['evenement_id' => $evenementId, 'serviceIds' => $serviceIds, 'promoCode' => $promoCode]);

            if (!$evenementId || !$serviceIds) {
                $this->logger->error('Missing evenement_id or services.', ['evenement_id' => $evenementId, 'serviceIds' => $serviceIds]);
                return new JsonResponse(['success' => false, 'error' => 'Événement ou services manquants.'], 400);
            }

            // Vérifier l'événement
            $this->logger->info('Fetching event', ['evenement_id' => $evenementId]);
            $evenement = $evenementRepository->find($evenementId);
            if (!$evenement) {
                $this->logger->error('Event not found.', ['evenement_id' => $evenementId]);
                return new JsonResponse(['success' => false, 'error' => 'Événement introuvable.'], 404);
            }

            // Vérifier les services
            $this->logger->info('Fetching services', ['serviceIds' => $serviceIds]);
            $services = $serviceRepository->findBy(['id_service' => $serviceIds]);
            $foundServiceIds = array_map(fn($service) => $service->getIdService(), $services);
            if (count($services) !== count($serviceIds) || array_diff($serviceIds, $foundServiceIds)) {
                $this->logger->error('Some services not found.', ['serviceIds' => $serviceIds, 'found' => $foundServiceIds]);
                return new JsonResponse(['success' => false, 'error' => 'Certains services sont introuvables.'], 400);
            }

            // Vérifier le code promo
            $codePromo = null;
            if ($promoCode) {
                $this->logger->info('Fetching promo code', ['promoCode' => $promoCode]);
                $codePromo = $codePromosRepository->findOneBy(['code' => $promoCode]);
                if (!$codePromo) {
                    $this->logger->error('Invalid promo code.', ['promoCode' => $promoCode]);
                    return new JsonResponse(['success' => false, 'error' => 'Code promotionnel invalide.'], 400);
                }
            }

            // Créer la réservation
            $this->logger->info('Creating reservation');
            $reservation = new Reservation();
            $reservation->setEvenement($evenement);
            $reservation->setStatut('non paye');
            $reservation->setCodePromos($codePromo);

            // Ajouter les services
            $this->logger->info('Adding services to reservation');
            foreach ($services as $service) {
                $reservation->addService($service);
            }

            // Vérifier le token CSRF
            $csrfToken = $request->headers->get('X-CSRF-TOKEN') ?: ($data['csrf_token'] ?? '');
            $this->logger->info('Checking CSRF token', ['csrfToken' => $csrfToken]);
            if (!$this->isCsrfTokenValid('reservation', $csrfToken)) {
                $this->logger->error('Invalid CSRF token.', ['csrfToken' => $csrfToken]);
                return new JsonResponse(['success' => false, 'error' => 'Token CSRF invalide.'], 403);
            }

            // Persister la réservation
            try {
                $this->logger->info('Persisting reservation.');
                $entityManager->persist($reservation);
                $entityManager->flush();
                $this->logger->info('Reservation created successfully.', ['reservation_id' => $reservation->getId()]);
                return new JsonResponse(['success' => true, 'message' => 'Réservation créée avec succès !']);
            } catch (\Exception $e) {
                $this->logger->error('Error during reservation persistence.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return new JsonResponse(['success' => false, 'error' => 'Erreur lors de la persistance de la réservation : ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in reservation creation.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return new JsonResponse(['success' => false, 'error' => 'Erreur inattendue : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('frontoffice/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }
}