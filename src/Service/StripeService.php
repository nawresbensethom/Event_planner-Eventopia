<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\ServiceRepository;

class StripeService
{
    private $params;
    private $serviceRepository;

    public function __construct(
        ParameterBagInterface $params,
        ServiceRepository $serviceRepository
    ) {
        $this->params = $params;
        $this->serviceRepository = $serviceRepository;

        // Initialize Stripe with the secret key
        Stripe::setApiKey($this->params->get('stripe_secret_key'));
    }

    public function createCheckoutSession(array $cart, float $total, string $successUrl, string $cancelUrl, ?string $promoCode = null): Session
    {
        if (empty($cart)) {
            throw new \Exception('Le panier est vide');
        }

        // Create a single line item with the discounted total
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Achat de services',
                    ],
                    'unit_amount' => (int)($total * 100), // Convert to centimes
                ],
                'quantity' => 1,
            ],
        ];

        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => 'fr',
        ];

        try {
            return Session::create($sessionData);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new \Exception('Erreur Stripe : ' . $e->getMessage());
        }
    }
}