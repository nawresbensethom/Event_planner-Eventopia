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
        // If user is not authenticated, redirect to login
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // If user is authenticated, redirect based on role
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        // Default redirect for other authenticated users
        return $this->redirectToRoute('app_home2');
    }
} 