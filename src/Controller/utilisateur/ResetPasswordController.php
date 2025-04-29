<?php

namespace App\Controller\utilisateur;

use App\Entity\Utilisateur;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;
    private $mailer;
    private $entityManager;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper, 
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, UtilisateurRepository $userRepository): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                try {
                    $resetToken = $this->resetPasswordHelper->generateResetToken($user);
                } catch (ResetPasswordExceptionInterface $e) {
                    $this->addFlash('reset_password_error', sprintf(
                        'There was a problem handling your password reset request - %s',
                        $e->getReason()
                    ));

                    return $this->redirectToRoute('app_forgot_password_request');
                }

                $email = (new Email())
                    ->from('noreply@eventopia.com')
                    ->to($user->getEmail())
                    ->subject('Your password reset request')
                    ->html($this->renderView('frontoffice/registration/reset_password_email.html.twig', [
                        'resetToken' => $resetToken,
                    ]));

                $this->mailer->send($email);

                $this->setTokenObjectInSession($resetToken);

                return $this->redirectToRoute('app_check_email');
            }

            $this->addFlash('reset_password_error', 'No account found with this email address.');
        }

        return $this->render('frontoffice/registration/forgot_password.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        $resetToken = $this->getTokenObjectFromSession();
        if (null === $resetToken) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('frontoffice/registration/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token = null): Response
    {
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setMot_de_passe($encodedPassword);
            $this->entityManager->flush();

            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Your password has been reset successfully.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('frontoffice/registration/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 