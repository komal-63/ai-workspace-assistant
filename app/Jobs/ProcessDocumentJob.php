<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentChunkService;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use App\Events\DocumentProcessed;
use Throwable;
use Illuminate\Support\Facades\Log;

class ProcessDocumentJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = 10;

    public function __construct(
        public Document $document,
        public bool $reextractOriginal = false
    ) {
    }

    public function handle(DocumentChunkService $chunkService, DocumentService $documentService): void
    {
        $this->document->update([
            'status' => 'processing',
        ]);

        if ($this->reextractOriginal) {
            if (! $this->document->file_path) {
                throw new \RuntimeException('Document has no original file to process.');
            }

            $filePath = Storage::disk('local')->path($this->document->file_path);

            if (! is_file($filePath)) {
                throw new \RuntimeException('Document original file was not found.');
            }

            $this->document->update([
                'content' => $documentService->extractText($filePath, $this->document->mime_type),
            ]);
        }

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

        Log::error('Document processing failed.', [
            'document_id' => $this->document->id,
            'user_id' => $this->document->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}