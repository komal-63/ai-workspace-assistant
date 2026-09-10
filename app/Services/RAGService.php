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
): bool {

    /*
    |--------------------------------------------------------------------------
    | 1. Deterministic routing for obvious document-specific questions
    |--------------------------------------------------------------------------
    |
    | These questions clearly ask for personal/employee facts that would
    | normally come from an uploaded document.
    |
    */

    $documentFactPatterns = [

        // Personal / employee fact questions
        '/\bwhat(?:\'s| is)\s+my\s+designation\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+department\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+salary\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+employee\s+id\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+employee\s+number\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+passport\s+number\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+job\s+title\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+joining\s+date\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+date\s+of\s+joining\b/i',
        '/\bwhat(?:\'s| is)\s+my\s+shift\s+timing\b/i',

        // Short natural questions
        '/\bmy\s+designation\s*\??\s*$/i',
        '/\bmy\s+department\s*\??\s*$/i',
        '/\bmy\s+salary\s*\??\s*$/i',
        '/\bmy\s+employee\s+id\s*\??\s*$/i',
        '/\bmy\s+passport\s+number\s*\??\s*$/i',
        '/\bmy\s+shift\s+timing\s*\??\s*$/i',

        // Employee document questions
        '/\bwhat(?:\'s| is)\s+(?:the\s+)?employee(?:\'s)?\s+designation\b/i',
        '/\bwhat(?:\'s| is)\s+(?:the\s+)?employee(?:\'s)?\s+department\b/i',
        '/\bwhat(?:\'s| is)\s+(?:the\s+)?employee(?:\'s)?\s+role\b/i',
        '/\bwhat(?:\'s| is)\s+(?:the\s+)?employee(?:\'s)?\s+salary\b/i',
        '/\bwhat(?:\'s| is)\s+(?:the\s+)?employee(?:\'s)?\s+shift\s+timing\b/i',
    ];

    foreach ($documentFactPatterns as $pattern) {

        if (preg_match($pattern, $question)) {

            Log::info('Document question classification', [
                'question' => $question,
                'classification' => 'DOCUMENT',
                'reason' => 'direct_document_fact_match',
            ]);

            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Build conversation history
    |--------------------------------------------------------------------------
    */

    $historyText = collect($history)
        ->map(function ($message) {
            return $message['role'] . ': ' . $message['content'];
        })
        ->implode("\n");

    /*
    |--------------------------------------------------------------------------
    | 3. Let AI classify ambiguous questions
    |--------------------------------------------------------------------------
    */

    $prompt = <<<PROMPT
You are a routing classifier for an AI workspace that supports uploaded documents
and normal general conversation.

Your task is to decide whether the CURRENT QUESTION should be answered using
uploaded documents.

Return ONLY one word:

DOCUMENT

or

GENERAL

RULES:

1. Classify as DOCUMENT when the user is asking to retrieve factual information
that may exist inside their uploaded documents.

Examples include:

- What is my designation?
- What is my employee ID?
- What is my salary?
- What is my department?
- What is my passport number?
- What is the employee's designation?
- What is the employee's role?
- What is her salary?
- What does the document say about my designation?

These should be DOCUMENT unless the requested information was explicitly supplied
by the user in the conversation history.

2. Classify as GENERAL when the question can naturally be answered without
uploaded documents.

Examples:

- What is Laravel?
- Explain dependency injection.
- What is the capital of France?
- How does Redis work?
- What does designation mean?
- What does an Admin Executive usually do?

3. If the user explicitly provided a fact in the conversation history and later
asks for that same fact, classify as GENERAL because conversation history already
contains the answer.

Example:

Conversation:
User: My designation is Manager.
Assistant: Okay.

Current question:
What is my designation?

Answer:
GENERAL

4. Do NOT assume that questions using "my", "me", "I", "his", "her", or similar
words are automatically GENERAL.

For example:

"What is my designation?"

should be DOCUMENT if the designation was not already explicitly stated in the
conversation.

5. Follow-up questions about previously retrieved document information must remain
DOCUMENT.

Example:

Conversation:
User: What is the employee's designation?
Assistant: Admin Executive.

Current question:
What about his role?

Answer:
DOCUMENT

Another example:

Conversation:
User: What is the employee's designation?
Assistant: Admin Executive.

Current question:
Explain that designation.

Answer:
DOCUMENT

6. If the user is STATING information rather than ASKING for information,
classify as GENERAL.

Examples:

"My designation is Manager."
"My employee ID is EMP-100."
"My phone number is 9876543210."

Answer:
GENERAL

Conversation history:
{$historyText}

Current question:
{$question}

Return ONLY:
DOCUMENT
or
GENERAL
PROMPT;

    /*
    |--------------------------------------------------------------------------
    | 4. Get AI classification
    |--------------------------------------------------------------------------
    */

    $response = $this->aiService->generateResponse([
        [
            'role' => 'user',
            'content' => $prompt,
        ],
    ]);

    $result = strtoupper(trim($response));

    /*
    |--------------------------------------------------------------------------
    | 5. Log classification
    |--------------------------------------------------------------------------
    */

    Log::info('Document question classification', [
        'question' => $question,
        'classification' => $result,
        'reason' => 'ai_classifier',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 6. Final routing decision
    |--------------------------------------------------------------------------
    */

    return $result === 'DOCUMENT';
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