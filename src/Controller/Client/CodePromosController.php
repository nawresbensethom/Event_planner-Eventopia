<?php

namespace App\Controller\Client;

use App\Entity\Client;
use App\Entity\Code_promos;
use App\Form\CodePromosType;
use App\Repository\Code_promosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Attribute\MapEntity;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/Client/code-promos')]
class CodePromosController extends AbstractController
{
    #[Route('/', name: 'app_client_code_promos_index', methods: ['GET'])]
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

        return $this->render('frontoffice/code_promos/index.html.twig', [
            'code_promos' => $code_promos,
        ]);
    }

    

    #[Route('/{id}', name: 'app_client_code_promos_show', methods: ['GET'])]
    public function show(#[MapEntity(class: Code_promos::class)] Code_promos $codePromo): Response
    {
        return $this->render('frontoffice/code_promos/show.html.twig', [
            'code_promo' => $codePromo,
        ]);
    }

    
} 