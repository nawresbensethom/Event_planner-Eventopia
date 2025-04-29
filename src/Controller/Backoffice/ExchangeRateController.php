<?php

namespace App\Controller\Backoffice;

use App\Service\ExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/exchange-rates')]
class ExchangeRateController extends AbstractController
{
    private $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    #[Route('/', name: 'backoffice_exchange_rates_index', methods: ['GET'])]
    public function index(): Response
    {
        $latestRates = $this->exchangeRateService->getLatestRates();
        
        return $this->render('backoffice/exchange_rates/index.html.twig', [
            'rates' => $latestRates
        ]);
    }

    #[Route('/historical/{date}', name: 'backoffice_exchange_rates_historical', methods: ['GET'])]
    public function historical(string $date): JsonResponse
    {
        $historicalRates = $this->exchangeRateService->getHistoricalRates($date);
        
        return $this->json($historicalRates);
    }
} 