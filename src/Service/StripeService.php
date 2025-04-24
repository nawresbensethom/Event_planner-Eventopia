<?php

namespace App\Service;

use App\Entity\Service;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private $router;
    private $params;
    private $stripeSecretKey;

    public function __construct(
        UrlGeneratorInterface $router,
        ParameterBagInterface $params,
        string $stripeSecretKey
    ) {
        $this->router = $router;
        $this->params = $params;
        $this->stripeSecretKey = $stripeSecretKey;
        
        // Initialize Stripe with the secret key
        Stripe::setApiKey($this->stripeSecretKey);
    }

    public function createCheckoutSession(Service $service): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $service->getTarif() * 100,
                    'product_data' => [
                        'name' => $service->getNom(),
                        'description' => $service->getDescription(),
                        'images' => [
                            $service->getPhotos() ? 'uploads/services/' . $service->getPhotos() : null,
                        ],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->router->generate('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->router->generate('app_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
} 