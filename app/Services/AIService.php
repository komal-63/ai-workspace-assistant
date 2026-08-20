<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function generateResponse($messages): string
    {
        $response = Http::withToken(config('services.groq.api_key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' =>config('services.groq.model'),
                'messages' => $messages,
            ]);

        return $response->json('choices.0.message.content');
    }

    public function generateSummary(string $existingSummary, array $messages): string
    {
        $conversationText = collect($messages)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

        $prompt = <<<PROMPT
        You are summarizing a conversation.

        Existing summary:
        {$existingSummary}

        Recent conversation:
        {$conversationText}

        Create a concise summary that preserves important context,
        user goals, decisions, preferences, and important facts.
        Do not include unnecessary details.
        PROMPT;

                $response = Http::withToken(config('services.groq.api_key'))
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => config('services.groq.model'),
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                    ]);

                return $response->json('choices.0.message.content');
    }

    public function generateAnswer(string $question, array $context): string
    {
            $contextText = collect($context)
                ->pluck('payload.content')
                ->filter()
                ->implode("\n\n");

            $prompt = <<<PROMPT
            You are an AI assistant for a workspace.

            Answer the user's question using the provided context.

            If the answer is not present in the context, say that the information is not available in the provided documents.

            Context:
            {$contextText}

            Question:
            {$question}
            PROMPT;

                $response = Http::withToken(config('services.groq.api_key'))
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => config('services.groq.model'),
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                    ]);

                $response->throw();

            return $response->json('choices.0.message.content');
    }

    public function generateGeneralAnswer(string $question): string
{
    $prompt = <<<PROMPT
    You are an AI assistant for a workspace.

    Answer the user's question naturally and accurately.

    Question:
    {$question}

    Answer:
    PROMPT;

        $response = Http::withToken(config('services.groq.api_key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        $response->throw();

        return $response->json('choices.0.message.content');
}
}