<?php
namespace App\Service;

use App\Entity\Plan;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PlanQrCodeGenerator
{
    private HttpClientInterface $client;
    private string $projectDir;
    private string $apiKey = '1eb0a84388a7465f848190237252802';

    public function __construct(HttpClientInterface $client, KernelInterface $kernel)
    {
        $this->client = $client;
        $this->projectDir = $kernel->getProjectDir();
    }

    public function generate(Plan $plan): string
    {
        $date = $plan->getDateDebut()->format('Y-m-d');
        $today = new \DateTimeImmutable('today');
        $isFuture = $plan->getDateDebut() > $today;
        $endpoint = $isFuture ? 'forecast.json?days=10' : 'history.json?dt=' . $date;
        $url = sprintf(
                      'http://api.weatherapi.com/v1/%s&key=%s&q=%s',
            $endpoint,
            $this->apiKey,
            urlencode($plan->getLocation())
        );

        $response = $this->client->request('GET', $url);
        $data = $response->toArray();
        $dayData = [];
        if (isset($data['forecast']['forecastday'])) {
            foreach ($data['forecast']['forecastday'] as $fd) {
                if ($fd['date'] === $date) {
                    $dayData = $fd['day'];
                    break;
                }
            }
        }
        if (empty($dayData) && isset($data['day'])) {
            $dayData = $data['day'];
        }
        $content = $dayData
            ? sprintf(
                "Date: %s\nMax: %.1f°C\nMin: %.1f°C\nCondition: %s",
                $date,
                $dayData['maxtemp_c'],
                $dayData['mintemp_c'],
                $dayData['condition']['text']
            )
            : "Pas de données météo pour le $date.";


        $qrCode = QrCode::create($content)
            ->setSize(150)
            ->setMargin(1);
           // Écriture de l'image QR Code 
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $uploadDir = $this->projectDir . '/public/uploads/qrcodes';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = sprintf('plan_%d.png', $plan->getId());
        $filePath = $uploadDir . '/' . $fileName;
        file_put_contents($filePath, $result->getString());
        return '/uploads/qrcodes/' . $fileName;
    }
}
