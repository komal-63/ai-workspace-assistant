<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private const SEMANTIC_LIMIT = 8;
    private const LEXICAL_LIMIT = 12;
    private const FINAL_CONTEXT_LIMIT = 5;

    public function __construct(
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService,
        private AIService $aiService
    ) {
    }

    public function retrieve(string $question, int $userId): array
    {
        $semantic = $this->semanticRetrieve($question, $userId, self::SEMANTIC_LIMIT, 0.0);
        $lexical = $this->lexicalRetrieve($question, $userId, self::LEXICAL_LIMIT);

        return $this->mergeCandidates($semantic, $lexical);
    }

    public function answer(string $question, int $userId, array $history = []): array
    {
        $isDocumentQuestion = $this->isDocumentQuestion($question, $history);

        if (! $isDocumentQuestion) {
            return [
                'source' => 'ai',
                'response' => $this->aiService->generateGeneralAnswer($question, $history),
                'document_id' => null,
            ];
        }

        $retrievalQuestion = $this->rewriteQuestionForRetrieval($question, $history);
        $context = $this->retrieve($retrievalQuestion, $userId);

        if (config('app.debug')) {
            Log::info('RAG retrieved context', [
                'question' => $retrievalQuestion,
                'results' => collect($context)->take(10)->map(function ($item) {
                    return [
                        'score' => $item['score'] ?? null,
                        'document_id' => $item['payload']['document_id'] ?? null,
                        'chunk_id' => $item['payload']['chunk_id'] ?? null,
                        'content' => mb_substr((string) ($item['payload']['content'] ?? ''), 0, 220),
                    ];
                })->all(),
            ]);
        }

        if (empty($context)) {
            return [
                'source' => 'not_found',
                'response' => $this->aiService->generateNotFoundAnswer($question),
                'document_id' => null,
            ];
        }

        $result = $this->aiService->generateGroundedAnswer($retrievalQuestion, $context, $history);

        if ($result['status'] === 'NONE') {
            return [
                'source' => 'not_found',
                'response' => $result['answer'],
                'document_id' => null,
            ];
        }

        return [
            'source' => 'document',
            'response' => $result['answer'],
            'document_id' => $this->resolveSupportingDocumentId($context, $result),
        ];
    }

    public function isDocumentQuestion(string $question, array $history = []): bool
    {
        $normalized = $this->normalizeQuestion($question);

        if ($this->matchesDocumentPattern($normalized)) {
            return true;
        }

        if ($this->matchesGeneralPattern($normalized)) {
            return false;
        }

        if ($this->looksLikeFollowUp($question, $history)) {
            return true;
        }

        if (empty($history)) {
            return false;
        }

        $historyText = $this->historyText($history);
        $prompt = <<<PROMPT
You are a routing classifier for uploaded-document questions vs general knowledge.
Return ONLY one word: DOCUMENT or GENERAL.

Conversation history:
{$historyText}

Current question:
{$question}
PROMPT;

        $response = $this->aiService->generateResponse([
            ['role' => 'user', 'content' => $prompt],
        ]);

        $result = strtoupper(trim($response));

        Log::info('Document question classification', [
            'question' => $question,
            'classification' => $result,
            'reason' => 'ai_classifier',
        ]);

        return $result === 'DOCUMENT';
    }

    public function rewriteQuestionForRetrieval(string $question, array $history = []): string
    {
        if (empty($history) || ! $this->needsRetrievalRewrite($question, $history)) {
            return $question;
        }

        $historyText = $this->historyText($history);

        $prompt = <<<PROMPT
You are rewriting a user's question for document retrieval.
Use the conversation history only to resolve references such as "he", "she", "his", "her", "it", "that", "this", or "that designation".
Rewrite the current question as a standalone question without answering it.
Return ONLY the rewritten question.

Conversation history:
{$historyText}

Current question:
{$question}
PROMPT;

        return trim($this->aiService->generateResponse([
            ['role' => 'user', 'content' => $prompt],
        ]));
    }

    public function mergeCandidates(array $semantic, array $lexical): array
    {
        $combined = [];

        foreach (array_merge($semantic, $lexical) as $candidate) {
            $payload = $candidate['payload'] ?? [];
            $documentId = (int) ($payload['document_id'] ?? 0);
            $chunkId = (int) ($payload['chunk_id'] ?? 0);

            if ($documentId <= 0 || $chunkId <= 0) {
                continue;
            }

            $key = $documentId . ':' . $chunkId;
            $score = (float) ($candidate['score'] ?? 0.0);

            if (! isset($combined[$key])) {
                $combined[$key] = [
                    'score' => $score,
                    'payload' => $payload,
                ];
                continue;
            }

            $combined[$key]['score'] = max($combined[$key]['score'], $score);
        }

        usort($combined, fn ($left, $right) => ($right['score'] ?? 0.0) <=> ($left['score'] ?? 0.0));

        return array_slice($combined, 0, self::FINAL_CONTEXT_LIMIT);
    }

    public function resolveSupportingDocumentId(array $context, array $result): ?int
    {
        $validDocumentIds = collect($context)
            ->map(fn ($item) => (int) ($item['payload']['document_id'] ?? 0))
            ->filter(fn ($documentId) => $documentId > 0)
            ->unique()
            ->values()
            ->all();

        $preferred = $result['supporting_document_id'] ?? null;
        if (is_numeric($preferred) && in_array((int) $preferred, $validDocumentIds, true)) {
            return (int) $preferred;
        }

        $chunkPreferred = $result['supporting_chunk_id'] ?? null;
        if (is_numeric($chunkPreferred)) {
            foreach ($context as $candidate) {
                if (((int) ($candidate['payload']['chunk_id'] ?? 0)) === (int) $chunkPreferred) {
                    return (int) ($candidate['payload']['document_id'] ?? 0);
                }
            }
        }

        return $validDocumentIds[0] ?? null;
    }

    private function semanticRetrieve(string $question, int $userId, int $limit, float $threshold): array
    {
        try {
            $questionVector = $this->embeddingService->generate($question);
            $results = $this->qdrantService->search($questionVector, $userId, $limit, $threshold);

            return collect($results)->map(function ($candidate) {
                $candidate['score'] = (float) ($candidate['score'] ?? 0.0);
                return $candidate;
            })->all();
        } catch (\Throwable $exception) {
            Log::warning('Semantic retrieval failed.', [
                'user_id' => $userId,
                'question' => $question,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function lexicalRetrieve(string $question, int $userId, int $limit): array
    {
        $terms = $this->extractLexicalTerms($question);

        if (empty($terms)) {
            return [];
        }

        $chunks = DocumentChunk::query()
            ->whereHas('document', fn ($query) => $query->where('user_id', $userId))
            ->select(['id', 'document_id', 'content'])
            ->get();

        $results = [];

        foreach ($chunks as $chunk) {
            $score = $this->scoreLexicalMatch($chunk->content, $terms);
            if ($score <= 0.0) {
                continue;
            }

            $results[] = [
                'score' => round($score, 4),
                'payload' => [
                    'document_id' => (int) $chunk->document_id,
                    'chunk_id' => (int) $chunk->id,
                    'content' => $chunk->content,
                ],
            ];
        }

        usort($results, fn ($left, $right) => ($right['score'] ?? 0.0) <=> ($left['score'] ?? 0.0));

        return array_slice($results, 0, $limit);
    }

    private function matchesDocumentPattern(string $question): bool
    {
        $patterns = [
            '/\b(?:what|which|who|when|where|how many|how much)\b.*\b(?:father|mother|parent|roll(?:\s+(?:no|number))?|employee\s+id|employee\s+number|designation|department|job\s+title|role|salary|school|class|subject|phone|email|date\s+of\s+birth|roll\s+no|roll\s+number|father\'s\s+name|mother\'s\s+name)\b/i',
            '/\b(?:my|his|her|their|the)\s+(?:father|mother|parent|designation|department|salary|roll(?:\s+(?:no|number))?|employee\s+id|school|subject|phone|email)\b/i',
            '/\b(?:what\s+is|what\s+are|what\'s|which)\s+(?:my|his|her|their)\s+.*\b(?:father|mother|designation|department|salary|school|roll|employee|subject)\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $question)) {
                return true;
            }
        }

        return false;
    }

    private function matchesGeneralPattern(string $question): bool
    {
        $patterns = [
            '/\b(?:what is|what\'s|who is|who\'s|explain|define|how does|how do|why does|why do|tell me about)\s+(?:laravel|redis|docker|python|php|dependency injection|capital of|history of|what does|what are)\b/i',
            '/\b(?:capital of|definition of|meaning of)\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $question)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeFollowUp(string $question, array $history): bool
    {
        if (empty($history)) {
            return false;
        }

        $text = strtolower($question);

        return preg_match('/\b(what about|tell me about|and his|and her|that|this|it|he|she|they|their)\b/i', $text) === 1;
    }

    private function needsRetrievalRewrite(string $question, array $history): bool
    {
        if (empty($history) || ! $this->looksLikeFollowUp($question, $history)) {
            return false;
        }

        $text = strtolower($question);

        return preg_match('/\b(what about|and his|and her|that|this|it|he|she|they|their)\b/i', $text) === 1;
    }

    private function historyText(array $history): string
    {
        return collect($history)
            ->take(-8)
            ->map(fn ($message) => ($message['role'] ?? 'user') . ': ' . ($message['content'] ?? ''))
            ->implode("\n");
    }

    private function normalizeQuestion(string $question): string
    {
        $normalized = strtolower(trim($question));
        $normalized = preg_replace('/[\x{2019}]/u', "'", $normalized);
        $normalized = preg_replace('/\s+/', ' ', (string) $normalized);

        return trim((string) $normalized);
    }

    private function extractLexicalTerms(string $question): array
    {
        $normalized = $this->normalizeQuestion($question);
        $aliases = [
            'father' => ['father', 'father\'s', 'father name', 'parent', 'mother'],
            'roll' => ['roll', 'roll no', 'roll number', 'rollno', 'number'],
            'designation' => ['designation', 'job title', 'role'],
            'department' => ['department', 'team', 'division'],
            'salary' => ['salary', 'pay', 'compensation'],
            'school' => ['school', 'college', 'institute'],
            'subject' => ['subject', 'course', 'paper'],
            'phone' => ['phone', 'mobile', 'contact'],
            'email' => ['email', 'mail'],
            'employee' => ['employee', 'staff', 'person'],
            'id' => ['id', 'identifier', 'number'],
        ];

        $terms = [];
        foreach ($aliases as $primary => $values) {
            foreach ($values as $value) {
                if (str_contains($normalized, $value)) {
                    $terms[] = $primary;
                }
            }
        }

        $tokens = preg_split('/[^a-z0-9\']+/i', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($tokens as $token) {
            $token = str_replace("'", '', strtolower($token));
            if (mb_strlen($token) > 2) {
                $terms[] = $token;
            }
        }

        $unique = [];
        foreach ($terms as $term) {
            $term = trim((string) $term);
            if ($term !== '' && ! in_array($term, $unique, true)) {
                $unique[] = $term;
            }
        }

        return $unique;
    }

    private function scoreLexicalMatch(string $content, array $terms): float
    {
        $normalizedContent = $this->normalizeQuestion($content);
        if ($normalizedContent === '') {
            return 0.0;
        }

        $score = 0.0;
        foreach ($terms as $term) {
            $needle = strtolower(trim($term));
            if ($needle === '') {
                continue;
            }

            if (str_contains($normalizedContent, $needle)) {
                $score += 0.4 + min(0.6, 0.08 * mb_strlen($needle));
            }
        }

        if ($score <= 0.0) {
            return 0.0;
        }

        $lengthBoost = min(1.0, mb_strlen($normalizedContent) / 500);

        return round($score * (0.8 + $lengthBoost), 4);
    }
}