<?php

namespace App\Services;

use App\Exceptions\AIServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            if (config('app.debug')) {
                Log::info('Groq raw response debug', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            $content = $response->json('choices.0.message.content');

            if (!is_string($content) || trim($content) === '') {
                throw new \RuntimeException('Groq returned an invalid or empty response.');
            }

            return $content;
        } catch (\Throwable $exception) {
            Log::error('Groq API request failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new AIServiceException(
                'AI service is currently unavailable.',
                0,
                $exception
            );
        }
    }

    private function consumeStream(string $rawStream, callable $onChunk): string
    {
        $buffer = '';
        $fullContent = '';

        $lines = preg_split('/\r\n|\n|\r/', $rawStream);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || ! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = trim(substr($line, 5));

            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }

            $decoded = json_decode($payload, true);

            if (! is_array($decoded)) {
                continue;
            }

            $delta = $decoded['choices'][0]['delta'] ?? [];
            $content = $delta['content'] ?? null;

            if (is_string($content) && $content !== '') {
                $fullContent .= $content;
                $onChunk($content);
            }
        }

        return $fullContent;
    }

    private function streamRequest(array $messages, callable $onChunk): string
    {
        try {
            $response = Http::withToken(config('services.groq.api_key'))
                ->timeout(60)
                ->withOptions(['stream' => true])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'temperature' => 0,
                    'stream' => true,
                ]);

            $response->throw();

            $psrResponse = $response->toPsrResponse();
            $stream = $psrResponse->getBody();
            $rawBuffer = '';
            $fullContent = '';

            while (! $stream->eof()) {
                $chunk = $stream->read(8192);

                if ($chunk === '') {
                    continue;
                }

                $rawBuffer .= $chunk;

                $lines = preg_split('/\r\n|\n|\r/', $rawBuffer);
                $rawBuffer = array_pop($lines) ?? '';

                foreach ($lines as $line) {
                    $line = trim($line);

                    if ($line === '' || ! str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $payload = trim(substr($line, 5));

                    if ($payload === '' || $payload === '[DONE]') {
                        continue;
                    }

                    $decoded = json_decode($payload, true);

                    if (! is_array($decoded)) {
                        continue;
                    }

                    $delta = $decoded['choices'][0]['delta'] ?? [];
                    $content = $delta['content'] ?? null;

                    if (is_string($content) && $content !== '') {
                        $fullContent .= $content;
                        $onChunk($content);
                    }
                }
            }

            if ($rawBuffer !== '') {
                $fullContent .= $this->consumeStream($rawBuffer, $onChunk);
            }

            if (trim($fullContent) === '') {
                throw new \RuntimeException('Groq stream returned an empty response.');
            }

            return $fullContent;
        } catch (\Throwable $exception) {
            Log::error('Groq streaming request failed.', [
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

    public function streamResponse(array $messages, callable $onChunk): string
    {
        return $this->streamRequest($messages, $onChunk);
    }

    public function generateSummary(string $existingSummary, array $messages): string
    {
        $conversationText = collect($messages)
            ->take(-12)
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
            ->take(5)
            ->map(fn ($item) => $item['payload']['content'] ?? '')
            ->filter()
            ->implode("\n\n");

        $historyText = collect($history)
            ->take(-6)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

        $prompt = <<<PROMPT
        You are a grounded AI assistant for a document-based workspace.

        Answer the user using only the provided document context.
        Use conversation history only to interpret follow-up references.

        Return ONLY valid JSON with this exact structure:
        {
            "status": "FULL",
            "answer": "Your answer here",
            "supporting_document_id": 123,
            "supporting_chunk_id": 456
        }

        Rules:
        1. status must be FULL, PARTIAL, or NONE.
        2. FULL: the document clearly answers the question.
        3. PARTIAL: limited relevant information is present but incomplete.
        4. NONE: answer must be exactly "I couldn't find this information in your uploaded documents." and the supporting IDs should be null.
        5. Never invent facts.
        6. If you include supporting IDs, they must match the document and chunk IDs present in the context exactly.
        7. If the answer is not supported by the provided context, return NONE.

        Conversation history:
        {$historyText}

        Current question:
        {$question}

        Document context:
        {$contextText}
        PROMPT;

        $response = $this->request([
            [
                'role' => 'system',
                'content' => 'You are a precise grounded document-answering assistant. Return strict JSON only.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ]);

        $cleanResponse = trim($response);
        $cleanResponse = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $cleanResponse);
        $result = json_decode($cleanResponse, true);

        if (!is_array($result) || !isset($result['status']) || !isset($result['answer'])) {
            throw new AIServiceException('AI returned an invalid grounded response.');
        }

        $status = strtoupper(trim((string) $result['status']));
        if (!in_array($status, ['FULL', 'PARTIAL', 'NONE'], true)) {
            throw new AIServiceException('AI returned an invalid relevance status.');
        }

        return [
            'status' => $status,
            'answer' => trim((string) $result['answer']),
            'supporting_document_id' => isset($result['supporting_document_id']) && is_numeric($result['supporting_document_id'])
                ? (int) $result['supporting_document_id']
                : null,
            'supporting_chunk_id' => isset($result['supporting_chunk_id']) && is_numeric($result['supporting_chunk_id'])
                ? (int) $result['supporting_chunk_id']
                : null,
        ];
    }

    public function generateGeneralAnswer(
        string $question,
        array $history = []
    ): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an AI assistant for a workspace. Answer the user naturally and accurately. Use the conversation history when it is relevant.',
            ],
        ];

        foreach (array_slice($history, -6) as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return $this->request($messages);
    }

    public function streamGeneralAnswer(string $question, array $history = [], callable $onChunk): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an AI assistant for a workspace. Answer the user naturally and accurately. Use the conversation history when it is relevant.',
            ],
        ];

        foreach (array_slice($history, -6) as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return $this->streamResponse($messages, $onChunk);
    }

    public function streamGroundedAnswer(
        string $question,
        array $context,
        array $history = [],
        callable $onChunk
    ): string
    {
        $contextText = collect($context)
            ->take(5)
            ->map(fn ($item) => $item['payload']['content'] ?? '')
            ->filter()
            ->implode("\n\n");

        $historyText = collect($history)
            ->take(-6)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

        $prompt = <<<PROMPT
You are a grounded AI assistant for a document-based workspace.

Answer the user using only the provided document context.
Use conversation history only to interpret follow-up references.

The response should be concise, natural, and directly answer the question.

Conversation history:
{$historyText}

Current question:
{$question}

Document context:
{$contextText}
PROMPT;

        return $this->streamResponse([
            [
                'role' => 'system',
                'content' => 'You are a precise grounded document-answering assistant. Answer using only the document context provided by the user.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ], $onChunk);
    }

    public function generateNotFoundAnswer(string $question): string
    {
        return "I couldn't find this information in your uploaded documents.";
    }
}