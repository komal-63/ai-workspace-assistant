<?php

namespace App\Services;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;

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

    public function answer(
    string $question,
    int $userId,
    array $history = []
    ): array
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Decide: General question or document question
        |--------------------------------------------------------------------------
        */

        $isDocumentQuestion = $this->isDocumentQuestion(
            $question,
            $history
        );

        /*
        |--------------------------------------------------------------------------
        | 2. General question
        |--------------------------------------------------------------------------
        */

        if (!$isDocumentQuestion) {
            return [
                'source' => 'ai',
                'response' => $this->aiService->generateGeneralAnswer(
                    $question,
                    $history
                ),
                'document_id' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Rewrite follow-up question for retrieval
        |--------------------------------------------------------------------------
        */

        $retrievalQuestion = $this->rewriteQuestionForRetrieval(
            $question,
            $history
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Search Qdrant
        |--------------------------------------------------------------------------
        */

        $context = $this->retrieve(
            $retrievalQuestion,
            $userId
        );

        /*
        |--------------------------------------------------------------------------
        | 5. Qdrant returned nothing
        |--------------------------------------------------------------------------
        */

        if (empty($context)) {
            return [
                'source' => 'not_found',
                'response' => $this->aiService->generateNotFoundAnswer(
                    $question
                ),
                'document_id' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Let final grounded AI evaluate context + answer
        |--------------------------------------------------------------------------
        */

        $result = $this->aiService->generateGroundedAnswer(
            $question,
            $context,
            $history
        );

        /*
        |--------------------------------------------------------------------------
        | 7. Context does not contain requested information
        |--------------------------------------------------------------------------
        */

        if ($result['status'] === 'NONE') {
            return [
                'source' => 'not_found',
                'response' => $result['answer'],
                'document_id' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 8. FULL or PARTIAL document answer
        |--------------------------------------------------------------------------
        */

        return [
            'source' => 'document',
            'response' => $result['answer'],
            'document_id' =>
                $context[0]['payload']['document_id'] ?? null,
        ];
    }

    public function isDocumentQuestion(
            string $question,
            array $history = []
        ): bool
            {
                $historyText = collect($history)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

              $prompt = <<<PROMPT
                Determine whether the user's current question is asking about information
                that should come from an uploaded document.

                Use the conversation history to understand follow-up questions.

                If the current question is a follow-up to a topic that was being discussed
                from an uploaded document, classify it as DOCUMENT.

                Questions containing references such as:
                "that designation", "that employee", "his role", "her salary",
                "what about it", "explain that", or similar follow-up references
                should use the conversation history to determine whether they refer
                to document information.

                Do not classify a follow-up as GENERAL merely because the current
                question can also be answered using general knowledge.

                If the user is providing or stating personal information rather than
                asking to retrieve it from a document, classify it as GENERAL.

                Examples of user-provided information:

                "My passport number is 2341."
                "My employee ID is EMP-100."
                "My designation is Manager."
                "My phone number is 9876543210."

                These are GENERAL because the user is telling you information in the
                conversation, not asking you to retrieve information from an uploaded
                document.

                However, if the user asks for information that should come from an
                uploaded document, classify it as DOCUMENT.

                Return ONLY one word:
                DOCUMENT
                or
                GENERAL

                Conversation history:
                {$historyText}

                Examples:

                Question: What is the employee's designation?
                Answer: DOCUMENT

                Question: What is the employee's passport number?
                Answer: DOCUMENT

                Question: What is the capital of France?
                Answer: GENERAL

                Question: Explain Laravel dependency injection.
                Answer: GENERAL

                Example conversation:
                User: My name is Komal.
                Assistant: Nice to meet you, Komal.
                Current question: What is my name?
                Answer: GENERAL

                Question: My passport number is 2341.
                Answer: GENERAL

                Question: What is the employee's passport number?
                Answer: DOCUMENT

                Current question:
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

    public function rewriteQuestionForRetrieval(
        string $question,
        array $history = []
    ): string {
        if (empty($history)) {
            return $question;
        }

        $historyText = collect($history)
            ->map(function ($message) {
                return $message['role'] . ': ' . $message['content'];
            })
            ->implode("\n");

            $prompt = <<<PROMPT
        You are rewriting a user's question for document retrieval.

        Use the conversation history to understand references such as:
        "he", "she", "it", "that", "this", "his", "her", or "that designation".

        Rewrite the current question as a standalone question that can be
        understood without seeing the conversation history.

        Do not answer the question.
        Do not add facts that are not present in the conversation.
        Return ONLY the rewritten question.

        Conversation history:
        {$historyText}

        Current question:
        {$question}

        Standalone question:
        PROMPT;

        return trim(
            $this->aiService->generateResponse([
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ])
        );
    }
}