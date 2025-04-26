<?php

namespace App\Controller\Client;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PaymentController extends AbstractController
{
    private $stripeService;
    private $entityManager;
    private $logger;

    public function __construct(
        StripeService $stripeService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->stripeService = $stripeService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/payment/checkout', name: 'payment_checkout', methods: ['POST'])]
    public function checkout(Request $request): JsonResponse
    {
        try {
            $session = $request->getSession();
            $cart = $session->get('cart', []);
            $promoCode = $session->get('promo_code');
            $promoDiscount = $session->get('promo_discount');

            if (empty($cart)) {
                return new JsonResponse([
                    'error' => 'Votre panier est vide'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Calculate subtotal and total
            $subtotal = 0;
            foreach ($cart as $id => $quantity) {
                $service = $this->entityManager->getRepository(Service::class)->find($id);
                if ($service) {
                    $subtotal += $service->getTarif() * $quantity;
                }
            }

            $total = $subtotal;
            $discountAmount = 0;
            if ($promoCode && $promoDiscount && isset($promoDiscount['value'])) {
                $discountAmount = ($subtotal * $promoDiscount['value']) / 100;
                $total = $subtotal - $discountAmount;
            }

            if ($total <= 0) {
                return new JsonResponse([
                    'error' => 'Montant invalide'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Log for debugging
            $this->logger->info('Creating Stripe checkout session', [
                'cart' => $cart,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'promo_code' => $promoCode
            ]);

            // Create Stripe checkout session
            $checkoutSession = $this->stripeService->createCheckoutSession(
                $cart,
                $total,
                $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                $promoCode
            );

            return new JsonResponse(['id' => $checkoutSession->id]);
        } catch (\Exception $e) {
            $this->logger->error('Checkout error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->getContent()
            ]);
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function success(Request $request): Response
    {
        try {
            $session = $request->getSession();

            // Verify cart exists
            if (!$session->has('cart')) {
                $this->addFlash('error', 'Session invalide');
                return $this->redirectToRoute('app_client_cart_index');
            }

            // Clear session
            $session->remove('cart');
            $session->remove('promo_code');
            $session->remove('promo_discount');

            $this->addFlash('success', 'Paiement effectué avec succès ! Merci pour votre commande.');
            return $this->render('frontoffice/payment/success.html.twig', [
                'message' => 'Paiement effectué avec succès !'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Payment success error: ' . $e->getMessage(), ['exception' => $e]);
            $this->addFlash('error', 'Une erreur est survenue lors de la finalisation du paiement.');
            return $this->redirectToRoute('app_client_cart_index');
        }
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('frontoffice/payment/failure.html.twig');
    }
}