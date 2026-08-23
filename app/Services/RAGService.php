<?php

namespace App\Services;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use App\Services\AIService;

class RAGService
{
    public function __construct(
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService,
        private AIService $aiService
    ) {
    }

    public function retrieve(string $question, int $userId): array
    {
        $questionVector = $this->embeddingService->generate($question);

        return $this->qdrantService->search(
            $questionVector,
            $userId,
            5
        );
    }

     public function answer(string $question, int $userId): array
    {
        $isDocumentQuestion = $this->isDocumentQuestion($question);

        // General question → directly AI
        if (!$isDocumentQuestion) {
            return [
                'source' => 'ai',
                'response' => $this->aiService->generateGeneralAnswer($question),
                'document_id' => null,
            ];
        }

        // Document-related question → search Qdrant
        $context = $this->retrieve($question, $userId);

        // Document question but answer not found
        

        if (!$this->isContextRelevant($question, $context)) {
            return [
                'source' => 'not_found',
                'response' => $this->aiService->generateNotFoundAnswer($question),
                'document_id' => null,
            ];
        }

        // Relevant document context found
        return [
            'source' => 'document',
            'response' => $this->aiService->generateAnswer(
                $question,
                $context
            ),
            'document_id' => $context[0]['payload']['document_id'] ?? null,
        ];
    }

    public function isDocumentQuestion(string $question): bool
    {
                $prompt = <<<PROMPT
            Determine whether the user's question is asking about information
            that could come from an uploaded document.

            Return ONLY one word:
            DOCUMENT
            or
            GENERAL

            Examples:

            Question: What is the employee's designation?
            Answer: DOCUMENT

            Question: What is the employee's passport number?
            Answer: DOCUMENT

            Question: What is the capital of France?
            Answer: GENERAL

            Question: Explain Laravel dependency injection.
            Answer: GENERAL

            User question:
            {$question}
            PROMPT;

                $response = $this->aiService->generateResponse([
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ]);

                return strtoupper(trim($response)) === 'DOCUMENT';
    }

    public function isContextRelevant(
            string $question,
            array $context
        ): bool {
        $contextText = collect($context)
            ->pluck('payload.content')
            ->filter()
            ->implode("\n\n");

        $prompt = <<<PROMPT
        Determine whether the provided document context contains enough information
        to answer the user's question.

        Return ONLY one word:
        YES
        or
        NO

        Question:
        {$question}

        Document context:
        {$contextText}
        PROMPT;

        $response = $this->aiService->generateResponse([
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ]);

        return strtoupper(trim($response)) === 'YES';
    }
}