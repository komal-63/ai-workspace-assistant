<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class DocumentService
{
    public function extractText(string $filePath, ?string $mimeType = null): string
    {
        $mimeType ??= mime_content_type($filePath) ?: null;

        if ($mimeType === 'text/plain') {
            return file_get_contents($filePath) ?: '';
        }

        if (! str_ends_with(strtolower($filePath), '.pdf') && $mimeType !== 'application/pdf') {
            return '';
        }

        $parser = new Parser();

        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }
}