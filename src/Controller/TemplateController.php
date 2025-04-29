<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\Service;
use App\Entity\SignalementPost;
use App\Entity\Code_promos;
use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Offreemploi;

class TemplateController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('frontoffice/base.html.twig');
    }

    #[Route('/backoffice/dashboard', name: 'app_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();
            
        $services = $entityManager
            ->getRepository(Service::class)
            ->findAll();
            
        $signalements = $entityManager
            ->getRepository(SignalementPost::class)
            ->findAll();
            
        $code_promos = $entityManager
            ->getRepository(Code_promos::class)
            ->findAll();

        // Utilisateurs actifs (prend en compte les différentes variations du statut)
        $connectedUsers = $entityManager
            ->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->where('LOWER(u.statut) IN (:statuts)')
            ->setParameter('statuts', ['active', 'actif', 'actifs'])
            ->getQuery()
            ->getResult();

        // Événements
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        // Offres de travail
        $offresTravail = $entityManager
            ->getRepository(Offreemploi::class)
            ->findAll();
        
        return $this->render('backoffice/dashboard.html.twig', [
            'posts' => $posts,
            'services' => $services,
            'signalements' => $signalements,
            'code_promos' => $code_promos,
            'connected_users' => $connectedUsers,
            'evenements' => $evenements,
            'offres_travail' => $offresTravail
        ]);
    }

    #[Route('/frontoffice', name: 'app_frontoffice')]
    public function frontoffice(): Response
    {
        return $this->render('frontoffice/home/index.html.twig');
    }

    #[Route('/frontoffice/body', name: 'app_frontoffice_body')]
    public function body(): Response
    {
        return $this->render('frontoffice/body.html.twig');
    }
} 