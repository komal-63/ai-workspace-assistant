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
                    'temperature' => 0,
                ]);

            $response->throw();

            \Illuminate\Support\Facades\Log::info('Groq raw response debug', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

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

    public function generateGroundedAnswer(
        string $question,
        array $context,
        array $history = []
    ): array
    {
        $contextText = collect($context)
            ->pluck('payload.content')
            ->filter()
            ->implode("\n\n");

        $historyText = collect($history)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

        $prompt = <<<PROMPT
        You are a grounded AI assistant for a document-based workspace.

        Your job is to answer the user's question using ONLY the provided
        document context for document-specific information.

        Use the conversation history only to understand follow-up references
        such as:
        "he", "she", "his", "her", "that", "this", "it",
        "that designation", "that employee", etc.

        You must classify the answer into one of these statuses:

        FULL
        - The document contains enough information to directly answer the question.

        PARTIAL
        - The document contains some relevant information, but does not contain
        enough detail to fully answer the question.

        NONE
        - The document does not contain information that answers or meaningfully
        relates to the requested information.

        Important rules:

        1. Never invent facts.

        2. Never add general knowledge to fill missing document information.

        3. If status is FULL:
        answer directly using the document.

        4. If status is PARTIAL:
        provide only the information available in the document and clearly
        explain what additional detail is not available.

        5. If status is NONE:
        the answer must be:
        "I couldn't find this information in your uploaded documents."

        6. Do not describe typical duties, responsibilities, salary,
        qualifications, or other details unless they actually appear
        in the provided document context.

        7. Return ONLY valid JSON.

        Use exactly this structure:

        {
            "status": "FULL",
            "answer": "Your answer here"
        }

        Conversation history:

        {$historyText}

        Current question:

        {$question}

        Document context:

        {$contextText}
        PROMPT;

        $response = $this->request([
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ]);

        // Sometimes LLMs return JSON inside ```json blocks.
        $cleanResponse = trim($response);

        $cleanResponse = preg_replace(
            '/^```(?:json)?\s*|\s*```$/i',
            '',
            $cleanResponse
        );

        $result = json_decode($cleanResponse, true);

        if (
            !is_array($result) ||
            !isset($result['status']) ||
            !isset($result['answer'])
        ) {
            throw new AIServiceException(
                'AI returned an invalid grounded response.'
            );
        }

        $status = strtoupper(trim($result['status']));

        if (!in_array($status, ['FULL', 'PARTIAL', 'NONE'], true)) {
            throw new AIServiceException(
                'AI returned an invalid relevance status.'
            );
        }

        return [
            'status' => $status,
            'answer' => trim($result['answer']),
        ];
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