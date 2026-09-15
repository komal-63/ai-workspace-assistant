<?php

use App\Models\Document;
use App\Services\DocumentChunkService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('documents:reprocess {document?} {--user=} {--force}', function (?int $document = null, ?int $user = null, bool $force = false) {
    $query = Document::query();

    if ($document) {
        $query->whereKey($document);
    }

    if ($user) {
        $query->where('user_id', $user);
    }

    $documents = $query->get();

    if ($documents->isEmpty()) {
        $this->warn('No documents matched the provided filters.');
        return Command::SUCCESS;
    }

    $count = 0;
    foreach ($documents as $documentModel) {
        try {
            $this->info('Reprocessing document #' . $documentModel->id . ' for user #' . $documentModel->user_id);
            app(DocumentChunkService::class)->rebuildForDocument($documentModel);
            $count++;
        } catch (\Throwable $exception) {
            $this->error('Failed to reprocess document #' . $documentModel->id . ': ' . $exception->getMessage());
        }
    }

    $this->info('Reprocessed ' . $count . ' of ' . $documents->count() . ' document(s).');

    return Command::SUCCESS;
})->purpose('Rebuild document chunks and vectors for selected documents');
