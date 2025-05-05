<?php

namespace App\Service;

use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use GuzzleHttp\Client;

class BrevoService
{
    private TransactionalEmailsApi $apiInstance;
    private string $apiKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->apiKey = $params->get('app.brevo_api_key');
        
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $this->apiKey);
        
        $client = new Client([
            'verify' => false // Désactive la vérification SSL
        ]);
        
        $this->apiInstance = new TransactionalEmailsApi($client, $config);
    }

    public function sendEmail(string $to, string $subject, string $htmlContent, ?string $from = null): void
    {
        $sendSmtpEmail = new SendSmtpEmail([
            'to' => [['email' => $to]],
            'htmlContent' => $htmlContent,
            'subject' => $subject,
            'sender' => ['email' => $from ?? 'oumaimahouimel4@gmail.com', 'name' => 'EvenTopia']
        ]);

        try {
            $this->apiInstance->sendTransacEmail($sendSmtpEmail);
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email: ' . $e->getMessage());
        }
    }
} 