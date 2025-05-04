<?php

namespace App\Controller\Client;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\ServiceRepository;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(
        Request $request,
        EvenementRepository $evenementRepository,
        PaginatorInterface $paginator
    ): Response {
        $searchTerm = $request->query->get('search');
        $category = $request->query->get('category');
        $evenements = $evenementRepository->findBySearchAndCategory($searchTerm, $category);
        $pagination = $paginator->paginate(
            $evenements,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('frontoffice/evenement/index.html.twig', [
            'pagination' => $pagination,
            'categories' => ['concert', 'conférence', 'atelier', 'exposition', 'autre'],
            'searchTerm' => $searchTerm,
            'selectedCategory' => $category,
        ]);
    }
    #[Route('/invitation/{id}', name: 'app_evenement_invitation', methods: ['GET'])]
    public function invitation(Evenement $evenement): Response
    {
        return $this->render('frontoffice/reservation/invitation.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $evenement = new Evenement();
        $evenement->setIdOrganisateur($this->getUser());
        $evenement->setStatut('non reserve');
        $evenement->setStatut2('en preparation');

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listeInvites = $form->get('liste_invites')->getData();
            if ($listeInvites) {
                $emails = array_filter(array_map('trim', $listeInvites));
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->addFlash('error', "L'email $email n'est pas valide.");
                        return $this->render('frontoffice/evenement/new.html.twig', [
                            'evenement' => $evenement,
                            'form' => $form->createView(),
                        ]);
                    }
                }
                $evenement->setListeInvites($emails);
            } else {
                $evenement->setListeInvites([]);
            }

            try {
                $entityManager->persist($evenement);
                $entityManager->flush();
                $this->addFlash('success', 'Événement créé avec succès !');
                return $this->redirectToRoute('app_evenement_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
                error_log('Database error: ' . $e->getMessage());
            }
        }

        return $this->render('frontoffice/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, ServiceRepository $serviceRepository): Response
    {
        return $this->render('frontoffice/evenement/show.html.twig', [
            'evenement' => $evenement,
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser() !== $evenement->getIdOrganisateur()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet événement.');
        }

        // Ensure required fields
        if (!$evenement->getStatut()) {
            $evenement->setStatut('non reserve');
        }
        if (!$evenement->getStatut2()) {
            $evenement->setStatut2('en preparation');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $listeInvites = $form->get('liste_invites')->getData();
                if ($listeInvites) {
                    $emails = array_filter(array_map('trim', $listeInvites));
                    foreach ($emails as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $this->addFlash('error', "L'email $email n'est pas valide.");
                            return $this->render('frontoffice/evenement/edit.html.twig', [
                                'evenement' => $evenement,
                                'form' => $form->createView(),
                            ]);
                        }
                    }
                    $evenement->setListeInvites($emails);
                } else {
                    $evenement->setListeInvites([]);
                }

                try {
                    $entityManager->flush();
                    $this->addFlash('success', 'Événement modifié avec succès !');
                    return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
                    error_log('Database error: ' . $e->getMessage());
                }
            } else {
                // Log validation errors
                $errors = $validator->validate($evenement);
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
                }
                $this->addFlash('error', 'Erreur de validation : ' . implode(', ', $errorMessages));
                error_log('Validation errors: ' . implode(', ', $errorMessages));
            }
        }

        return $this->render('frontoffice/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser() !== $evenement->getIdOrganisateur()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cet événement.');
        }

        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($evenement);
                $entityManager->flush();
                $this->addFlash('success', 'Événement supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
                error_log('Database error: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/search', name: 'app_evenement_search', methods: ['POST'])]
    public function search(
        Request $request,
        EvenementRepository $evenementRepository,
        PaginatorInterface $paginator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return new JsonResponse(['error' => 'Données JSON invalides'], 400);
            }

            $searchTerm = $data['search'] ?? null;
            $category = $data['category'] ?? null;
            $page = $data['page'] ?? 1;

            $evenements = $evenementRepository->findBySearchAndCategory($searchTerm, $category);
            $pagination = $paginator->paginate(
                $evenements,
                $page,
                10
            );

            $results = [];
            foreach ($pagination as $evenement) {
                $results[] = [
                    'id' => $evenement->getId(),
                    'nom_evenement' => $evenement->getNomEvenement(),
                    'description' => $evenement->getDescription(),
                    'category' => $evenement->getCategory(),
                    'image' => $evenement->getImage(),
                    'date' => $evenement->getDateEvenement()->format('d/m/Y'),
                    'lieu' => $evenement->getVille() ?? 'Non spécifié',
                    'id_organisateur' => $evenement->getIdOrganisateur() ? $evenement->getIdOrganisateur()->getId() : null,
                ];
            }

            return new JsonResponse([
                'results' => $results,
                'totalItems' => $pagination->getTotalItemCount(),
                'currentPage' => $pagination->getCurrentPageNumber(),
                'itemsPerPage' => $pagination->getItemNumberPerPage(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}