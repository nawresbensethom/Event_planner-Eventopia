<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getStatusColor', [$this, 'getStatusColor']),
        ];
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'Published' => 'success',
            'Brouillon' => 'warning',
            'En_attente' => 'info',
            'Rejected' => 'danger',
            default => 'secondary',
        };
    }
} 