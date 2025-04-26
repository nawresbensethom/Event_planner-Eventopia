<?php
namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    public function createCheckoutSession(array $cart, float $total, string $successUrl, string $cancelUrl): Session
    {
        if (empty($cart)) {
            throw new \Exception('Le panier est vide');
        }

        $lineItems = [];
        foreach ($cart as $id => $quantity) {
            $service = $this->serviceRepository->find($id);
            if (!$service) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $service->getNom(),
                        'description' => $service->getDescription(),
                    ],
                    'unit_amount' => (int)($service->getTarif() * 100), // Convertir en centimes
                ],
                'quantity' => $quantity,
            ];
        }

        if (empty($lineItems)) {
            throw new \Exception('Aucun service valide dans le panier');
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => 'fr',
            'currency' => 'eur',
        ]);
    }
}