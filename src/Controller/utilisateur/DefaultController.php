<?php

namespace App\Controller\utilisateur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        // Always redirect to login page for the root URL
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // If somehow a user is already authenticated, redirect appropriately
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->redirectToRoute('app_home2');
    }
} 