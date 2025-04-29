<?php

namespace App\Controller\Backoffice;

use App\Entity\Post;
use App\Entity\Service;
use App\Entity\SignalementPost;
use App\Entity\Code_promos;
use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all required data
        $posts = $entityManager->getRepository(Post::class)->findAll();
        $services = $entityManager->getRepository(Service::class)->findAll();
        $signalements = $entityManager->getRepository(SignalementPost::class)->findAll();
        $code_promos = $entityManager->getRepository(Code_promos::class)->findAll();
        $users = $entityManager->getRepository(Utilisateur::class)->findAll();
        $events = $entityManager->getRepository(Evenement::class)->findAll();
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        return $this->render('backoffice/dashboard.html.twig', [
            'posts' => $posts,
            'services' => $services,
            'signalements' => $signalements,
            'code_promos' => $code_promos,
            'users' => $users,
            'events' => $events,
            'reservations' => $reservations,
            'controller_name' => 'DashboardController'
        ]);
    }

    #[Route('/backoffice-dashboard', name: 'admin_backoffice_dashboard')]
    public function backofficeDashboard(EntityManagerInterface $entityManager): Response
    {
        // Fetch all required data
        $posts = $entityManager->getRepository(Post::class)->findAll();
        $services = $entityManager->getRepository(Service::class)->findAll();
        $signalements = $entityManager->getRepository(SignalementPost::class)->findAll();
        $code_promos = $entityManager->getRepository(Code_promos::class)->findAll();
        $users = $entityManager->getRepository(Utilisateur::class)->findAll();
        $events = $entityManager->getRepository(Evenement::class)->findAll();
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        return $this->render('backoffice/dashboard.html.twig', [
            'posts' => $posts,
            'services' => $services,
            'signalements' => $signalements,
            'code_promos' => $code_promos,
            'users' => $users,
            'events' => $events,
            'reservations' => $reservations,
            'controller_name' => 'DashboardController'
        ]);
    }
} 