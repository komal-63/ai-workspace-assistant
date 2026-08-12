<?php

namespace App\Services;

use App\Models\Document;

class DocumentChunkService
{
    public function createChunks(Document $document): void
    {
        $content = trim($document->content);

        $chunks = str_split($content, 1000);

        foreach ($chunks as $index => $chunk) {

            $document->chunks()->create([
                'chunk_index' => $index,
                'content' => trim($chunk),
            ]);
        }
    }
}