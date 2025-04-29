<?php

namespace App\Controller\Client;

use App\Entity\Offreemploi;
use App\Entity\Utilisateur;
use App\Form\OffreemploiType;
use App\Repository\OffreemploiRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Candidature;
use App\Form\CandidatureType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/client/offreemploi')]
final class OffreemploiController extends AbstractController
{
    #[Route(name: 'client_offreemploi_index', methods: ['GET'])]
    public function index(OffreemploiRepository $offreemploiRepository): Response
    {
        return $this->render('frontoffice/offreemploi/index.html.twig', [
            'offreemplois' => $offreemploiRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'client_offreemploi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Offreemploi instance
        $offreemploi = new Offreemploi();

        // Set the 'id_user' to the user with id = 1 (from the database)
        $user = $entityManager->getRepository(Utilisateur::class)->find(1);

        if ($user) {
            // Set the user as the id_user
            $offreemploi->setIdUser($user);
        } else {
            // Handle the case where the user with id 1 doesn't exist
            // You might want to throw an exception or handle this case gracefully
            throw $this->createNotFoundException('User not found.');
        }

        // Create and handle the form
        $form = $this->createForm(OffreemploiType::class, $offreemploi);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new offer to the database
            $entityManager->persist($offreemploi);
            $entityManager->flush();

            // Redirect to the index page after saving the offer
            return $this->redirectToRoute('client_offreemploi_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form view if not submitted or not valid
        return $this->render('frontoffice/offreemploi/new.html.twig', [
            'offreemploi' => $offreemploi,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id_offre}', name: 'client_offreemploi_show', methods: ['GET'])]
    public function show(Offreemploi $offreemploi): Response
    {
        return $this->render('frontoffice/offreemploi/show.html.twig', [
            'offreemploi' => $offreemploi,
        ]);
    }


    #[Route('/{id_offre}/edit', name: 'client_offreemploi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offreemploi $offreemploi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreemploiType::class, $offreemploi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('client_offre_emploi_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/offreemploi/edit.html.twig', [
            'offreemploi' => $offreemploi,
            'form' => $form,
        ]);
    }

    #[Route('/{id_offre}', name: 'client_offreemploi_delete', methods: ['POST'])]
    public function delete(Request $request, Offreemploi $offreemploi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offreemploi->getIdOffre(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offreemploi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('client_offre_emploi_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/backoffice/dashboard', name: 'client_offre_emploi_dashboard', methods: ['GET'])]
    public function dashboard(OffreemploiRepository $offreemploiRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);

        // Get the search term from the request (if available)
        $search = $request->query->get('search', '');

        // Build the query to fetch job offers
        $queryBuilder = $offreemploiRepository->createQueryBuilder('o');

        // If there is a search term, filter the query
        if ($search) {
            $queryBuilder
                ->where('o.titre_poste LIKE :search')
                ->orWhere('o.lieu LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Get the query from the builder
        $query = $queryBuilder->getQuery();

        // Paginate the results
        $offreemplois = $paginator->paginate(
            $query,  // Query
            $page,   // Current page number
            10       // Number of results per page
        );

        // Statistics
        $totalOffers = $offreemploiRepository->count([]);
        $activeOffers = $offreemploiRepository->createQueryBuilder('o')
            ->where('o.date_limite IS NULL OR o.date_limite >= :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();

        $expiredOffers = $offreemploiRepository->createQueryBuilder('o')
            ->where('o.date_limite < :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();

        // Offers by location
        $offersByLocation = $offreemploiRepository->createQueryBuilder('o')
            ->select('o.lieu, COUNT(o.id_offre) as offer_count')
            ->groupBy('o.lieu')
            ->getQuery()
            ->getResult();

        // Offers by job title
        $offersByJobTitle = $offreemploiRepository->createQueryBuilder('o')
            ->select('o.titre_poste, COUNT(o.id_offre) as offer_count')
            ->groupBy('o.titre_poste')
            ->getQuery()
            ->getResult();

        // Raw query for Offers by Salary Range (corrected version)
        $connection = $offreemploiRepository->getEntityManager()->getConnection();
        $sql = '
            SELECT 
                CASE 
                    WHEN salaire <= 1000 THEN "0-1000"
                    WHEN salaire BETWEEN 1001 AND 3000 THEN "1001-3000"
                    WHEN salaire BETWEEN 3001 AND 5000 THEN "3001-5000"
                    WHEN salaire > 5000 THEN ">5000"
                END AS salary_range, 
                COUNT(id_offre) as offer_count 
            FROM offreemploi 
            GROUP BY salary_range
        ';

        $stmt = $connection->prepare($sql);
        $stmt->executeQuery();  // Corrected method to execute the query
        // Average salary
        $averageSalary = $offreemploiRepository->createQueryBuilder('o')
            ->select('AVG(o.salaire) as avg_salary')
            ->getQuery()
            ->getSingleScalarResult();

        // Pass the current page, search term, paginated results, and statistics to the template
        return $this->render('frontoffice/offreemploi/dashboard.html.twig', [
            'offreemplois' => $offreemplois,
            'currentPage' => $page,
            'search' => $search,
            'totalOffers' => $totalOffers,
            'activeOffers' => count($activeOffers),
            'expiredOffers' => count($expiredOffers),
            'offersByLocation' => $offersByLocation,
            'offersByJobTitle' => $offersByJobTitle,
            'averageSalary' => $averageSalary,
            'matchingOffers' => $search ? count($offreemplois) : 0
        ]);
    }

    #[Route('/offreemploi/{id_offre}/candidature', name: 'client_candidature_apply', methods: ['GET', 'POST'])]
public function apply(
    Request $request,
    EntityManagerInterface $entityManager,
    Offreemploi $offreemploi
): Response {
    $candidature = new Candidature();
    $form = $this->createForm(CandidatureType::class, $candidature);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $candidature->setOffre($offreemploi);
        $candidature->setPrestataire($this->getUser());
        $candidature->setStatut('En attente');

        $cvFile = $form->get('cv')->getData();
        if ($cvFile) {
            $newFilename = uniqid() . '.' . $cvFile->guessExtension();
            $cvFile->move($this->getParameter('cvs_directory'), $newFilename);
            $candidature->setCv($newFilename);
        }

        $entityManager->persist($candidature);
        $entityManager->flush();

        $this->addFlash('success', 'Votre candidature a été envoyée avec succès.');
        return $this->redirectToRoute('client_offreemploi_index');
    }

    return $this->render('frontoffice/candidature/apply.html.twig', [
        'offreemploi' => $offreemploi,
        'form' => $form->createView(),
    ]);
}

}
