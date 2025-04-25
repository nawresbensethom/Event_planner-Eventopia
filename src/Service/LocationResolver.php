<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocationResolver
{
    private HttpClientInterface $client;
    private string $apiKey = '1eb0a84388a7465f848190237252802';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCoordinates(string $location): array
    {
        $response = $this->client->request('GET', 'http://api.weatherapi.com/v1/current.json', [
            'query' => [
                'key' => $this->apiKey,
                'q'   => $location,
            ],
        ]);

        $data = $response->toArray();

        return [
            'lat' => $data['location']['lat'],
            'lon' => $data['location']['lon'],
        ];
    }
}