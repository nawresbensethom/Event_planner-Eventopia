<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;

class TemplateController extends AbstractController
{
    #[Route('/template', name: 'app_template')]
    public function index(): Response
    {
        return $this->render('frontoffice/base.html.twig');
    }

    #[Route('/backoffice/dashboard', name: 'dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();
        
        return $this->render('backoffice/dashboard.html.twig', [
            'posts' => $posts
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