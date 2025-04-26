<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateService
{
    private $httpClient;
    private $baseUrl = 'https://api.exchangerate.host';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getExchangeRates(string $base = 'EUR'): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/latest', [
            'query' => [
                'base' => $base
            ]
        ]);

        return json_decode($response->getContent(), true);
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/convert', [
            'query' => [
                'from' => $from,
                'to' => $to,
                'amount' => $amount
            ]
        ]);

        $data = json_decode($response->getContent(), true);
        return $data['result'] ?? $amount;
    }
} 