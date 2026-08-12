<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class DocumentService
{
    public function extractText(string $filePath): string
    {
        $parser = new Parser();

        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }
}