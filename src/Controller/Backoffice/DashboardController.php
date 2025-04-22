<?php

namespace App\Controller\Backoffice;

use App\Entity\Post;
use App\Entity\Service;
use App\Entity\SignalementPost;
use App\Entity\CodePromo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/backoffice')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all required data
        $posts = $entityManager->getRepository(Post::class)->findAll();
        $services = $entityManager->getRepository(Service::class)->findAll();
        $signalements = $entityManager->getRepository(SignalementPost::class)->findAll();
        $code_promos = $entityManager->getRepository(CodePromo::class)->findAll();

        return $this->render('backoffice/dashboard.html.twig', [
            'posts' => $posts,
            'services' => $services,
            'signalements' => $signalements,
            'code_promos' => $code_promos
        ]);
    }
} 