<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateService
{
    private $httpClient;
    private $apiKey;
    private $baseUrl = 'http://api.exchangeratesapi.io/v1';

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getLatestRates(string $base = 'EUR'): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/latest', [
            'query' => [
                'access_key' => $this->apiKey,
                'base' => $base,
                'symbols' => 'USD,GBP,JPY,AUD,CAD,CHF,CNY,TND'
            ]
        ]);

        $data = $response->toArray();
        
        // Ajout du dinar tunisien avec un taux fixe (à ajuster selon vos besoins)
        $data['rates']['TND'] = 3.25; // Taux approximatif 1 EUR = 3.25 TND
        
        return $data;
    }

    public function getHistoricalRates(string $date, string $base = 'EUR'): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/' . $date, [
            'query' => [
                'access_key' => $this->apiKey,
                'base' => $base,
                'symbols' => 'USD,GBP,JPY,AUD,CAD,CHF,CNY,TND'
            ]
        ]);

        $data = $response->toArray();
        
        // Ajout du dinar tunisien avec un taux fixe pour les données historiques
        $data['rates']['TND'] = 3.25;
        
        return $data;
    }
}