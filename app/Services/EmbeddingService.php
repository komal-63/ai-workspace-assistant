<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    public function generate(string $text): array
    {
        try {
            $response = Http::timeout(60)
                ->post('http://127.0.0.1:8001/embed', [
                    'text' => $text,
                ]);

            $response->throw();

            $vector = $response->json('vector');

            if (!is_array($vector) || empty($vector)) {
                throw new \RuntimeException(
                    'Embedding service returned an invalid vector.'
                );
            }

            return $vector;

        } catch (\Throwable $exception) {

            Log::error('Embedding service failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

       
    }
}