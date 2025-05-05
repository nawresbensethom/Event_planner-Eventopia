<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiApiService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $apiUrl,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->logger = $logger;
    }

    public function generatePostIdea(
        string $theme, 
        array $existingTitles = [], 
        string $audience = 'générale'
    ): string {
        // Construction du prompt contextuel
        $prompt = "Tu es un assistant spécialisé pour un blog avec les caractéristiques suivantes :\n";
        $prompt .= "- Les articles (Posts) peuvent recevoir des commentaires\n";
        $prompt .= "- Les lecteurs peuvent signaler des contenus inappropriés (SignalementPost)\n";
        $prompt .= "- Audience cible : $audience\n\n";
        
        if (!empty($existingTitles)) {
            $prompt .= "Articles existants (à ne pas dupliquer) :\n";
            foreach ($existingTitles as $title) {
                $prompt .= "- $title\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "Génère une idée d'article qui :\n";
        $prompt .= "1. Traite du thème : '$theme'\n";
        $prompt .= "2. Encourage les interactions positives (commentaires)\n";
        $prompt .= "3. Minimise les risques de signalements\n";
        $prompt .= "4. Comprend :\n";
        $prompt .= "   a) Un titre accrocheur et unique\n";
        $prompt .= "   b) Un résumé (50 mots max)\n";
        $prompt .= "   c) 3 questions pour lancer la discussion\n";
        $prompt .= "Langue : français, style : professionnel mais accessible";
    
        $this->logger->info('Sending request to Gemini API with prompt: ' . $prompt);
        
        try {
            $response = $this->makeRequest([
                'contents' => [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]);
            
            $this->logger->info('Received response from Gemini API');
            return $response['candidates'][0]['content']['parts'][0]['text'] ?? 'Erreur de génération';
        } catch (\Exception $e) {
            $this->logger->error('Error calling Gemini API: ' . $e->getMessage());
            return 'Une erreur est survenue lors de la génération de l\'idée. Veuillez réessayer.';
        }
    }

    private function makeRequest(array $data): array
    {
        $url = $this->apiUrl;
        
        $this->logger->info('Making request to: ' . $url);
        $this->logger->info('Request data: ' . json_encode($data));

        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => $this->apiKey
                ],
                'json' => $data
            ]
        );

        $statusCode = $response->getStatusCode();
        $this->logger->info('Response status code: ' . $statusCode);
        
        if ($statusCode !== 200) {
            $this->logger->error('Error response: ' . $response->getContent(false));
            throw new \Exception('API request failed with status code: ' . $statusCode);
        }

        return $response->toArray();
    }
}