<?php

namespace App\Services;

use App\Models\Document;

class DocumentChunkService
{
    public function __construct(
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService
    ) {
    }

    public function createChunks(Document $document): void
    {
        $content = trim($document->content);

        $chunks = str_split($content, 1000);

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
}