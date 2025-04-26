<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Service;
use App\Entity\Code_promos;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Psr\Log\LoggerInterface;

#[Route('/client')]
class CartController extends AbstractController
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['GET', 'POST'])]
    public function cartAdd(Request $request, int $id): Response
    {
        try {
            $service = $this->entityManager->getRepository(Service::class)->find($id);
            if (!$service) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Service non trouvé'
                    ], Response::HTTP_NOT_FOUND);
                }
                $this->addFlash('error', 'Service non trouvé');
                return $this->redirectToRoute('app_client_cart_index');
            }

            $session = $request->getSession();
            $cart = $session->get('cart', []);

            $cart[$id] = ($cart[$id] ?? 0) + 1;
            $session->set('cart', $cart);

            $cartCount = array_sum($cart);

            $this->logger->info('Service added to cart', [
                'service_id' => $id,
                'cart' => $cart,
                'cart_count' => $cartCount
            ]);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Service ajouté au panier',
                    'cartCount' => $cartCount
                ]);
            }

            $this->addFlash('success', 'Service ajouté au panier');
            return $this->redirectToRoute('app_client_cart_index');
        } catch (\Exception $e) {
            $this->logger->error('Error adding service to cart', [
                'service_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Une erreur est survenue'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout au panier');
            return $this->redirectToRoute('app_client_cart_index');
        }
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart(Request $request, int $id): Response
    {
        try {
            $session = $request->getSession();
            $cart = $session->get('cart', []);

            if (!isset($cart[$id])) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Service non trouvé dans le panier'
                    ], Response::HTTP_NOT_FOUND);
                }
                $this->addFlash('error', 'Service non trouvé dans le panier');
                return $this->redirectToRoute('app_client_cart_index');
            }

            unset($cart[$id]);
            $session->set('cart', $cart);

            // Remove promo code if cart is empty
            if (empty($cart)) {
                $session->remove('promo_code');
                $session->remove('promo_discount');
            }

            $cartCount = array_sum($cart);

            $this->logger->info('Service removed from cart', [
                'service_id' => $id,
                'cart' => $cart,
                'cart_count' => $cartCount
            ]);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Service supprimé du panier',
                    'cartCount' => $cartCount
                ]);
            }

            $this->addFlash('success', 'Service supprimé du panier');
            return $this->redirectToRoute('app_client_cart_index');
        } catch (\Exception $e) {
            $this->logger->error('Error removing service from cart', [
                'service_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Une erreur est survenue'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du service');
            return $this->redirectToRoute('app_client_cart_index');
        }
    }

    #[Route('/cart/count', name: 'app_cart_count', methods: ['GET'])]
    public function getCartCount(Request $request): JsonResponse
    {
        try {
            $session = $request->getSession();
            $cart = $session->get('cart', []);
            $cartCount = array_sum($cart);

            return new JsonResponse([
                'success' => true,
                'cartCount' => $cartCount
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving cart count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Une erreur est survenue'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/cart/apply-promo', name: 'app_cart_apply_promo', methods: ['POST'])]
    public function applyPromoCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $promoCode = $data['promo_code'] ?? null;
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (!$promoCode) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Veuillez entrer un code promo'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $codePromo = $this->entityManager->getRepository(Code_promos::class)
                ->findOneBy(['code' => $promoCode]);

            if (!$codePromo) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Code promo invalide'
                ], Response::HTTP_BAD_REQUEST);
            }

            $now = new DateTime();
            if ($now < $codePromo->getDateDebut() || $now > $codePromo->getDateExpiration()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Code promo expiré'
                ], Response::HTTP_BAD_REQUEST);
            }

            $serviceId = array_keys($cart)[0] ?? null;
            if (!$serviceId || !$codePromo->getService() || $codePromo->getService()->getIdService() != $serviceId) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Ce code promo n\'est pas valide pour ce service'
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($codePromo->getLimiteUtilisation() <= 0) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Ce code promo a atteint sa limite d\'utilisation'
                ], Response::HTTP_BAD_REQUEST);
            }

            $reductionValue = (int)str_replace('%', '', $codePromo->getReductionType());

            $session->set('promo_code', $promoCode);
            $session->set('promo_discount', [
                'type' => 'percentage',
                'value' => $reductionValue
            ]);

            $codePromo->setLimiteUtilisation($codePromo->getLimiteUtilisation() - 1);
            $this->entityManager->flush();

            $service = $this->entityManager->getRepository(Service::class)->find($serviceId);
            $quantity = $cart[$serviceId];
            $originalTotal = $service->getTarif() * $quantity;
            $reduction = ($originalTotal * $reductionValue) / 100;
            $newTotal = $originalTotal - $reduction;

            return new JsonResponse([
                'success' => true,
                'message' => 'Code promo appliqué avec succès',
                'discount_type' => 'percentage',
                'discount_value' => $reductionValue,
                'original_total' => $originalTotal,
                'reduction' => $reduction,
                'new_total' => $newTotal
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'application du code promo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la vérification du code promo'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/cart', name: 'app_client_cart_index')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $items = [];
        $total = 0;
        $subtotal = 0;

        foreach ($cart as $id => $quantity) {
            $service = $this->entityManager->getRepository(Service::class)->find($id);
            if ($service) {
                $items[] = [
                    'service' => $service,
                    'quantity' => $quantity
                ];
                $subtotal += $service->getTarif() * $quantity;
            }
        }

        $total = $subtotal;
        $discountAmount = 0;
        $promoDiscount = $session->get('promo_discount');
        $promoCode = $session->get('promo_code');

        if ($promoCode && $promoDiscount && isset($promoDiscount['value'])) {
            $discountAmount = ($subtotal * $promoDiscount['value']) / 100;
            $total = $subtotal - $discountAmount;
        }

        return $this->render('frontoffice/cart/index.html.twig', [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $total,
            'discount_amount' => $discountAmount,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'promo_code' => $promoCode
        ]);
    }
}