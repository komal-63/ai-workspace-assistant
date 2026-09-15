<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

class DocumentChunkService
{
    public function __construct(
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService
    ) {
    }

    public function createChunks(Document $document): void
    {
        $content = trim((string) $document->content);

        if ($content === '') {
            return;
        }

        $document->chunks()->delete();
        $this->qdrantService->deleteByDocument($document->id);

        $chunks = $this->buildChunks($content);

        foreach ($chunks as $index => $chunk) {
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $documentChunk = $document->chunks()->create([
                'chunk_index' => $index,
                'content' => $chunk,
            ]);

            $vector = $this->embeddingService->generate($chunk);

            $this->qdrantService->store(
                $documentChunk->id,
                $vector,
                [
                    'user_id' => $document->user_id,
                    'document_id' => $document->id,
                    'chunk_id' => $documentChunk->id,
                    'content' => $chunk,
                ]
            );
        }
    }

    public function rebuildForDocument(Document $document): void
    {
        $this->createChunks($document);
    }

    public function buildChunks(string $text, int $targetSize = 1000, int $overlap = 150): array
    {
        $normalized = preg_replace("/\r\n/", "\n", $text);
        $normalized = preg_replace('/\n{3,}/', "\n\n", (string) $normalized);
        $normalized = trim((string) $normalized);

        if ($normalized === '') {
            return [];
        }

        $paragraphs = preg_split('/\n{2,}/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        $paragraphs = array_map('trim', $paragraphs);

        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $safeParagraph = preg_replace('/\s+/', ' ', $paragraph);
            $safeParagraph = trim((string) $safeParagraph);

            if ($safeParagraph === '') {
                continue;
            }

            $candidate = $buffer === '' ? $safeParagraph : $buffer . "\n\n" . $safeParagraph;

            if (mb_strlen($candidate) <= $targetSize) {
                $buffer = $candidate;
                continue;
            }

            if ($buffer !== '') {
                $chunks[] = $buffer;
                $buffer = $this->tailOverlap($buffer, $targetSize, $overlap);
            }

            $parts = $this->splitParagraphIntoSizedParts($safeParagraph, $targetSize, $overlap);

            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part !== '') {
                    $chunks[] = $part;
                }
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        $filtered = [];

        foreach ($chunks as $chunk) {
            $chunk = trim((string) $chunk);
            if ($chunk === '') {
                continue;
            }

            if (mb_strlen($chunk) < 120) {
                $filtered[] = $chunk;
                continue;
            }

            $filtered[] = $chunk;
        }

        return array_values(array_filter(array_map('trim', $filtered), fn ($value) => $value !== ''));
    }

    private function splitParagraphIntoSizedParts(string $paragraph, int $targetSize, int $overlap): array
    {
        $parts = [];
        $sentences = preg_split('/(?<=[.!?])\s+|\n+/', $paragraph, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($sentences)) {
            $sentences = [$paragraph];
        }

        $current = '';
        $carry = '';

        foreach ($sentences as $sentence) {
            $candidate = $current === '' ? $sentence : $current . ' ' . $sentence;

            if (mb_strlen($candidate) <= $targetSize) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $parts[] = $current;
                $carry = $this->tailOverlap($current, $targetSize, $overlap);
                $current = $carry . ' ' . $sentence;
                continue;
            }

            $words = preg_split('/\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY) ?: [$sentence];
            $buffer = '';

            foreach ($words as $word) {
                $next = $buffer === '' ? $word : $buffer . ' ' . $word;
                if (mb_strlen($next) > $targetSize && $buffer !== '') {
                    $parts[] = $buffer;
                    $carry = $this->tailOverlap($buffer, $targetSize, $overlap);
                    $buffer = $carry . ' ' . $word;
                    continue;
                }

                $buffer = $next;
            }

            if ($buffer !== '') {
                $current = $buffer;
            }
        }

        if (trim((string) $current) !== '') {
            $parts[] = $current;
        }

        return $parts;
    }

    private function tailOverlap(string $text, int $targetSize, int $overlap): string
    {
        $clean = trim((string) $text);
        $length = mb_strlen($clean);
        $window = max(1, min($targetSize, $length));
        $start = max(0, $length - $window);
        $tail = mb_substr($clean, $start);

        if ($overlap > 0 && mb_strlen($tail) > $overlap) {
            return trim(mb_substr($tail, -min($overlap, mb_strlen($tail))));
        }

        return trim($tail);
    }
}