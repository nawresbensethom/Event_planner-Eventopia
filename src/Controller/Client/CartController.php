<?php

namespace App\Controller\Client;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Code_promos;

#[Route('/client/cart')]
class CartController extends AbstractController
{
    private $serviceRepository;
    private $params;

    public function __construct(ServiceRepository $serviceRepository, ParameterBagInterface $params)
    {
        $this->serviceRepository = $serviceRepository;
        $this->params = $params;
    }

    #[Route('/', name: 'app_cart_index')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $service = $this->serviceRepository->find($id);
            if (!$service) {
                continue;
            }

            // Récupérer le tarif brut
            $tarif = $service->getTarif();
            
            // Nettoyer et convertir le tarif
            if (is_string($tarif)) {
                $tarif = str_replace(['€', ' ', ','], ['', '', '.'], $tarif);
            }
            $tarif = (float) $tarif;

            // Calculer le sous-total pour cet article
            $sousTotal = $tarif * $quantity;
            $total += $sousTotal;

            $cartItems[] = [
                'service' => $service,
                'quantity' => $quantity,
                'sousTotal' => $sousTotal
            ];
        }

        // Stocker le total dans la session pour Stripe
        $session->set('cart_total', $total);

        return $this->render('frontoffice/cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total,
            'stripe_public_key' => $this->params->get('stripe_public_key')
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(int $id, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'message' => 'Service ajouté au panier avec succès !',
            'cartCount' => array_sum($cart)
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Service retiré du panier avec succès !');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $session->remove('promo_code');

        $this->addFlash('success', 'Panier vidé avec succès !');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/verify-promo', name: 'app_verify_promo', methods: ['POST'])]
    public function verifyPromoCode(
        Request $request, 
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $promoCode = $data['promoCode'] ?? null;

        if (!$promoCode) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Code promo manquant'
            ]);
        }

        $codePromo = $entityManager->getRepository(Code_promos::class)
            ->findOneBy(['code' => $promoCode]);

        if (!$codePromo) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Code promo invalide'
            ]);
        }

        // Vérifier si le code promo est valide (dates)
        $now = new \DateTime();
        if ($now < $codePromo->getDateDebut() || $now > $codePromo->getDateExpiration()) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Ce code promo a expiré ou n\'est pas encore valide'
            ]);
        }

        // Extraire le pourcentage de réduction du type de réduction (ex: "30%" -> 30)
        $reduction = (int)str_replace('%', '', $codePromo->getReductionType());

        $session->set('promo_code', [
            'code' => $promoCode,
            'reduction' => $reduction
        ]);

        return new JsonResponse([
            'valid' => true,
            'reduction' => $reduction,
            'message' => 'Code promo appliqué avec succès !'
        ]);
    }
} 