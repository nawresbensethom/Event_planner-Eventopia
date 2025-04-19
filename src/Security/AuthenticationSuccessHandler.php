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
        // Redirection par défaut pour tous les utilisateurs
        $targetPath = $request->getSession()->get('_security.main.target_path');
        
        if ($targetPath) {
            $request->getSession()->remove('_security.main.target_path');
            return new RedirectResponse($targetPath);
        }

        // Si pas de targetPath, rediriger selon le rôle
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } elseif ($this->security->isGranted('ROLE_PRESTATAIRE') || $this->security->isGranted('ROLE_ORGANISATEUR')) {
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        // Redirection par défaut si aucun rôle spécifique
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
} 