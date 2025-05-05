<?php

namespace App\Controller\Backoffice;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\BrevoEmailService;

#[Route('/backoffice/evenement')]
final class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index_back', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository, PaginatorInterface $paginator): Response
    {
        // Paramètres de recherche, tri et filtre
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_evenement');
        $direction = $request->query->get('direction', 'asc');
        $categorie = $request->query->get('categorie', '');

        // Construire la requête dynamique
        $queryBuilder = $evenementRepository->createQueryBuilder('e');

        if ($search) {
            $queryBuilder->andWhere('e.nom_evenement LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if ($categorie) {
            $queryBuilder->andWhere('e.category = :categorie')
                         ->setParameter('categorie', $categorie);
        }

        // Gestion du tri
        $validSortFields = ['date_evenement', 'statut'];
        $validDirections = ['asc', 'desc'];
        if (in_array($sort, $validSortFields) && in_array($direction, $validDirections)) {
            $queryBuilder->orderBy('e.' . $sort, strtoupper($direction));
        } else {
            $queryBuilder->orderBy('e.date_evenement', 'ASC'); // Tri par défaut
        }

        // Pagination
        $evenements = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 // Nombre par page
        );

        // Gestion des requêtes AJAX
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // Rendu sans le layout de base pour AJAX
            return $this->render('backoffice/evenement/index.html.twig', [
                'evenements' => $evenements,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'categorie' => $categorie,
            ], null); // Pas de layout pour les requêtes AJAX
        }

        return $this->render('backoffice/evenement/index.html.twig', [
            'evenements' => $evenements,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_showback', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('backoffice/evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/evenement/{id}/update-statut', name: 'evenement_update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Evenement $evenement, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['statut']) || !in_array($data['statut'], ['reserve', 'non reserve'])) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid statut'], 400);
        }
        $evenement->setStatut($data['statut']);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/evenement/{id}/update-statut2', name: 'evenement_update_statut2', methods: ['POST'])]
    public function updateStatut2(Request $request, Evenement $evenement, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['statut2']) || !in_array($data['statut2'], ['en preparation', 'en cours', 'terminé'])) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid statut2'], 400);
        }
        $evenement->setStatut2($data['statut2']);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'app_evenement_deleteback', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'Événement supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_evenement_index_back', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/send-invitations', name: 'app_evenement_send_invitations', methods: ['POST'])]
    public function sendInvitations(Request $request, Evenement $evenement, BrevoEmailService $emailService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('send_invitations' . $evenement->getId(), $request->request->get('_token'))) {
            $successCount = 0;
            $failCount = 0;

            foreach ($evenement->getListeInvites() as $email) {
                if ($emailService->sendEventInvitation($evenement, $email)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            if ($successCount > 0) {
                $this->addFlash('success', "{$successCount} invitation(s) envoyée(s) avec succès.");
            }
            if ($failCount > 0) {
                $this->addFlash('error', "{$failCount} invitation(s) n'ont pas pu être envoyées.");
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_evenement_showback', ['id' => $evenement->getId()]);
    }
}