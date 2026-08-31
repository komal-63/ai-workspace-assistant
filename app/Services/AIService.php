<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Exceptions\AIServiceException;

class AIService
{

    private function request(array $messages): string
    {
        try {
            $response = Http::withToken(config('services.groq.api_key'))
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                ]);

            $response->throw();

            $content = $response->json('choices.0.message.content');

            if (!is_string($content) || trim($content) === '') {
                throw new \RuntimeException(
                    'Groq returned an invalid or empty response.'
                );
            }

            return $content;

        } 
        catch (\Throwable $exception) {

            \Illuminate\Support\Facades\Log::error('Groq API request failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new AIServiceException(
                'AI service is currently unavailable.',
                0,
                $exception
            );
        }
    }

    public function generateResponse(array $messages): string
    {
        return $this->request($messages);
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

                return $this->request([
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ]);
    }

    public function generateAnswer(
    string $question,
    array $context,
    array $history = []
): string
{
    $contextText = collect($context)
        ->pluck('payload.content')
        ->filter()
        ->implode("\n\n");

    $messages = $history;

    $messages[] = [
        'role' => 'system',
        'content' => <<<PROMPT
You are an AI assistant for a workspace.

Use the conversation history and the provided document context
to answer the user's current question.

If the answer is not present in the provided document context
and the question specifically depends on the document,
say that the information is not available in the provided documents.

Document context:
{$contextText}
PROMPT,
    ];

    $messages[] = [
        'role' => 'user',
        'content' => $question,
    ];

    return $this->request($messages);
}

    public function generateGeneralAnswer(
        string $question,
        array $history = []
    ): string
    {
        $messages = $history;

        $messages[] = [
            'role' => 'system',
            'content' => 'You are an AI assistant for a workspace. Answer the user naturally and accurately. Use the conversation history when it is relevant.',
        ];

        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return $this->request($messages);
    }

    public function generateNotFoundAnswer(string $question): string
    {
        return "I couldn't find this information in your uploaded documents.";
    }
}