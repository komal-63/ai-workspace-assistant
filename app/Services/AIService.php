<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class AIService
{
    public function generateResponse(string $message): string
    {
        $response = Http::withToken(config('services.groq.api_key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ]);
        dd($response);
        return $response->json('choices.0.message.content');
    }
}