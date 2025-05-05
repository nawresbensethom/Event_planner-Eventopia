<?php

namespace App\Controller\Backoffice;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backoffice/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index_back', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $reservationRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');

        $queryBuilder = $reservationRepository->createQueryBuilder('r')
            ->leftJoin('r.evenement', 'e')
            ->addSelect('e');

        if ($search) {
            $queryBuilder->andWhere('e.nom_evenement LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        // Définition des champs de tri valides avec leurs alias de jointure
        $validSortFields = [
            'montant_total' => 'r.montantTotal',
            'statut' => 'r.statut',
        ];

        $validDirections = ['asc', 'desc'];
        
        if (isset($validSortFields[$sort]) && in_array($direction, $validDirections)) {
            $queryBuilder->orderBy($validSortFields[$sort], strtoupper($direction));
        } else {
            $queryBuilder->orderBy('r.id', 'ASC');
        }

        $reservations = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('backoffice/reservation/index.html.twig', [
                'reservations' => $reservations,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ], null);
        }

        return $this->render('backoffice/reservation/index.html.twig', [
            'reservations' => $reservations,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_deleteback', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_reservation_index_back', [], Response::HTTP_SEE_OTHER);
    }
}