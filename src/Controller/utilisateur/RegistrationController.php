<?php
namespace App\Controller\utilisateur;

use App\Entity\Utilisateur;
use App\Entity\Profil;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Psr\Log\LoggerInterface;

class RegistrationController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger selon son rôle
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            return $this->redirectToRoute('app_home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontoffice/registration/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error ? $error->getMessageKey() : null,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        

        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $formErrors = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Hash password
                $user->setMot_de_passe(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Set role
                $role = $form->get('role')->getData();
                if (!in_array($role, ['ROLE_PRESTATAIRE', 'ROLE_ORGANISATEUR', 'ROLE_ADMIN'])) {
                    throw new \InvalidArgumentException('Rôle invalide');
                }
                $user->setRole($role);

                // Set role-specific fields
                match ($role) {
                    'ROLE_PRESTATAIRE' => $user->setSpecialite(
                        $form->has('specialite') ? $form->get('specialite')->getData() : null
                    ),
                    'ROLE_ORGANISATEUR' => $user->setPreferences(
                        $form->has('preferences') ? $form->get('preferences')->getData() : null
                    ),
                    'ROLE_ADMIN' => $user->setPrivileges(
                        $form->has('privileges') ? $form->get('privileges')->getData() : null
                    ),
                };

                // Create profile
                $profil = new Profil();
                $profil->setUser($user)
                    ->setDescription($form->has('description') ? $form->get('description')->getData() : null)
                    ->setAdresse($form->has('adresse') ? $form->get('adresse')->getData() : null);

                $entityManager->persist($user);
                $entityManager->persist($profil);
                $entityManager->flush();

                $this->addFlash('success', 'Compte créé avec succès !');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $logger->error('Registration error: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors de la création du compte');
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $formErrors[] = $error->getMessage();
            }
        }

        return $this->render('frontoffice/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
            'page_title' => 'Inscription',
            'roles' => [
                'ROLE_ADMIN' => 'Administrateur',
                'ROLE_PRESTATAIRE' => 'Prestataire de services',
                'ROLE_ORGANISATEUR' => 'Organisateur d\'événements'
            ]
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        // Create a form to handle email input
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->getForm();

        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            // Here you would typically check if the email exists in the database
            // And send the reset email if the email exists
            // This is where the password reset logic will go.

            // For now, we simulate sending a reset link
            $this->addFlash('success', 'If this email exists, a reset link has been sent.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('frontoffice/registration/forgot_password.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Le logout est géré automatiquement par Symfony Security
        // Cette méthode ne sera jamais exécutée
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route("/dashboard", name: "admin_dashboard")]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Check if the user has the admin role
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'accéder à cette page.');
            return $this->redirectToRoute('app_home');
        }

        // Récupérer les organisateurs
        $organisateurs = $entityManager->getRepository(Utilisateur::class)
            ->findBy(['role' => 'ROLE_ORGANISATEUR']);

        // Récupérer les prestataires
        $prestataires = $entityManager->getRepository(Utilisateur::class)
            ->findBy(['role' => 'ROLE_PRESTATAIRE']);

        return $this->render('backoffice/admin/dashboard.html.twig', [
            'organisateurs' => $organisateurs,
            'prestataires' => $prestataires
        ]);
    }
}
