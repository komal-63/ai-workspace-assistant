<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        } catch (\Throwable $exception) {

            \Illuminate\Support\Facades\Log::error('Groq API request failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
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

                return $this->request([
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ]);
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

            return $this->request([
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ]);
    }

    public function generateNotFoundAnswer(string $question): string
    {
        return "I couldn't find this information in your uploaded documents.";
    }
}