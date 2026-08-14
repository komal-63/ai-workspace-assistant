<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    public function generate(string $text): array
    {
        $response = Http::timeout(60)
            ->post('http://127.0.0.1:8000/embed', [
                'text' => $text,
            ]);

        $response->throw();

        return $response->json('vector');
    }
}