<?php
namespace App\Service;

use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extractTextFromFile(string $filePath): string
    {
        return (new TesseractOCR($filePath))->run();
    }

    public function extractSkillsSection(string $text): ?string
    {
        if (preg_match('/skills[:\s]*(.*)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
