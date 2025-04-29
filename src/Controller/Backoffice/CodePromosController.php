<?php

namespace App\Controller\Backoffice;

use App\Entity\Code_promos;
use App\Form\CodePromosType;
use App\Repository\Code_promosRepository;
use App\Service\BrevoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Attribute\MapEntity;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/backoffice/code-promos')]
class CodePromosController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_code_promos_index', methods: ['GET'])]
    public function index(Request $request, Code_promosRepository $codePromosRepository, PaginatorInterface $paginator): Response
    {
        $query = $codePromosRepository->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->getQuery();

        $code_promos = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
         3
        );

        return $this->render('backoffice/code_promos/index.html.twig', [
            'code_promos' => $code_promos,
        ]);
    }

    #[Route('/new', name: 'app_backoffice_code_promos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, BrevoService $brevoService): Response
    {
        $codePromo = new Code_promos();
        $form = $this->createForm(CodePromosType::class, $codePromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($codePromo);
            $entityManager->flush();

            // Envoi de l'email de notification
            try {
                $htmlContent = sprintf(
                    '<h1>Nouveau code promo créé !</h1>
                    <p>Un nouveau code promotionnel a été créé avec les détails suivants :</p>
                    <ul>
                        <li><strong>Code :</strong> %s</li>
                        <li><strong>Réduction :</strong> %s</li>
                        <li><strong>Date de début :</strong> %s</li>
                        <li><strong>Date d\'expiration :</strong> %s</li>
                        <li><strong>Description :</strong> %s</li>
                    </ul>
                    <p>Vous pouvez le gérer depuis votre espace d\'administration.</p>',
                    $codePromo->getCode(),
                    $codePromo->getReductionType(),
                    $codePromo->getDateDebut()->format('d/m/Y'),
                    $codePromo->getDateExpiration()->format('d/m/Y'),
                    $codePromo->getDescription() ?? 'Aucune description'
                );

                $brevoService->sendEmail(
                    'oumaimahouimel41@yahoo.fr', // Remplacez par l'email de l'administrateur
                    'Nouveau code promo créé - ' . $codePromo->getCode(),
                    $htmlContent
                );
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Le code promo a été créé mais l\'email de notification n\'a pas pu être envoyé : ' . $e->getMessage());
            }

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