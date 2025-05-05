<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Clear any existing session data that might cause loops
        $session = $request->getSession();
        $session->remove('_security.main.target_path');
        
        // Get user's roles
        $roles = $token->getRoleNames();
        
        // Determine redirect based on role
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } 
        
        // For both ROLE_PRESTATAIRE and ROLE_ORGANISATEUR, redirect to app_home2
        if (in_array('ROLE_PRESTATAIRE', $roles) || in_array('ROLE_ORGANISATEUR', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_home2'));
        }

        // Default fallback
        return new RedirectResponse($this->urlGenerator->generate('app_home2'));
    }
} 