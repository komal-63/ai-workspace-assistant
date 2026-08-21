<?php

namespace App\Listeners;

use App\Events\DocumentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendDocumentProcessedNotification implements ShouldQueue
{
    public function handle(DocumentProcessed $event): void
    {
        Log::info('Document processed successfully.', [
            'document_id' => $event->document->id,
            'user_id' => $event->document->user_id,
        ]);
    }
}