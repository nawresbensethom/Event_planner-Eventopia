<?php
namespace App\Controller\utilisateur;

use App\Entity\Utilisateur;
use App\Entity\Profil;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger selon son rôle
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            return $this->redirectToRoute('app_home2');
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

                // Set initial status as inactive until email verification
                $user->setStatut('inactive');

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

                // Generate a signed URL and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('eventopia@example.com', 'Eventopia Bot'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('frontoffice/registration/confirmation_email.html.twig')
                );

                $this->addFlash('success', 'Un email de confirmation vous a été envoyé. Veuillez vérifier votre boîte de réception.');
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

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home2');
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
}
