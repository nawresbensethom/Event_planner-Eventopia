<?php

namespace App\Controller\Client;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    #[Route('/payment/checkout', name: 'payment_checkout', methods: ['POST'])]
    public function checkout(Request $request): JsonResponse
    {
        try {
            $session = $request->getSession();
            $cart = $session->get('cart', []);
            $total = $session->get('cart_total', 0);

            if (empty($cart) || $total <= 0) {
                throw new \Exception('Panier vide ou montant invalide');
            }

            $checkoutSession = $this->stripeService->createCheckoutSession(
                $cart,
                $total,
                $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
            );

            return new JsonResponse(['id' => $checkoutSession->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function success(Request $request): Response
    {
        try {
            $session = $request->getSession();
            
            // Vérifier si le panier existe
            if (!$session->has('cart')) {
                throw new \Exception('Session invalide');
            }

            // Nettoyer la session
            $session->remove('cart');
            $session->remove('cart_total');

            $this->addFlash('success', 'Paiement effectué avec succès ! Merci pour votre commande.');
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la finalisation du paiement.');
            return $this->redirectToRoute('app_cart_index');
        }
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
        return $this->redirectToRoute('app_cart_index');
    }
} 