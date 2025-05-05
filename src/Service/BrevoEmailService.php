<?php

namespace App\Service;

use App\Entity\Evenement;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class BrevoEmailService
{
    private $apiInstance;
    private $senderEmail;
    private $senderName;

    public function __construct(ParameterBagInterface $params)
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $params->get('brevo_api_key'));

        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        $this->senderEmail = $params->get('brevo_sender_email');
        $this->senderName = $params->get('brevo_sender_name');
    }

    public function sendEventInvitation(Evenement $evenement, string $recipientEmail): bool
    {
        try {
            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail->setTo([['email' => $recipientEmail]]);
            $sendSmtpEmail->setSubject("Invitation à l'événement : " . $evenement->getNomEvenement());
            $sendSmtpEmail->setHtmlContent($this->generateEmailContent($evenement));
            $sendSmtpEmail->setSender(['name' => $this->senderName, 'email' => $this->senderEmail]);

            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            return true;
        } catch (\Exception $e) {
            // Log l'erreur
            error_log('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return false;
        }
    }

    private function generateEmailContent(Evenement $evenement): string
    {
        $date = $evenement->getDateEvenement()->format('d/m/Y H:i');
        $lieu = $evenement->getRue() ? 
            $evenement->getRue() . ', ' . $evenement->getCodePostal() . ' ' . $evenement->getVille() : 
            'Lieu à confirmer';

        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Invitation à l'événement</h1>
                    </div>
                    <div class='content'>
                        <p>Cher(e) invité(e),</p>
                        <p>Vous êtes cordialement invité(e) à l'événement suivant :</p>
                        
                        <h2>{$evenement->getNomEvenement()}</h2>
                        
                        <p><strong>Date :</strong> {$date}</p>
                        <p><strong>Lieu :</strong> {$lieu}</p>
                        <p><strong>Description :</strong></p>
                        <p>{$evenement->getDescription()}</p>
                        
                        <p>Nous espérons vous y voir nombreux !</p>
                        
                        <p>Cordialement,<br>
                        L'équipe d'organisation</p>
                    </div>
                    <div class='footer'>
                        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
} 