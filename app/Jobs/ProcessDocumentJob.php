<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentChunkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\DocumentProcessed;
use Throwable;

class ProcessDocumentJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = 10;

    public function __construct(
        public Document $document
    ) {
    }

    public function handle(DocumentChunkService $chunkService): void
    {
        $this->document->update([
            'status' => 'processing',
        ]);

        $chunkService->createChunks($this->document);

        $this->document->update([
            'status' => 'completed',
        ]);

        event(new DocumentProcessed($this->document));
    }

    public function failed(Throwable $exception): void
    {
        $this->document->update([
            'status' => 'failed',
        ]);
    }
}