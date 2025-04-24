<?php

namespace App\Controller\Client;

use App\Entity\Service;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/client/payment')]
class PaymentController extends AbstractController
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    #[Route('/create-checkout-session/{id}', name: 'app_payment_checkout')]
    public function checkout(Service $service): JsonResponse
    {
        try {
            $checkout_session = $this->stripeService->createCheckoutSession($service);
            return new JsonResponse(['id' => $checkout_session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/success', name: 'app_payment_success')]
    public function success(): Response
    {
        return $this->render('frontoffice/payment/success.html.twig');
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('frontoffice/payment/cancel.html.twig');
    }
} 