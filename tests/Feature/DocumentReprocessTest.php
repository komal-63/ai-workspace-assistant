<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DocumentReprocessTest extends TestCase
{
    use RefreshDatabase;

    public function test_reprocess_rebuilds_only_the_selected_documents_chunks_and_vectors(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Student Record',
            'file_path' => 'documents/student.txt',
            'mime_type' => 'text/plain',
            'content' => 'Old extracted content',
            'status' => 'completed',
        ]);

        $otherDocument = Document::create([
            'user_id' => $otherUser->id,
            'title' => 'Other Record',
            'file_path' => 'documents/other.txt',
            'mime_type' => 'text/plain',
            'content' => 'Other user content',
            'status' => 'completed',
        ]);

        Storage::disk('local')->put('documents/student.txt', "Roll number: 1018640148\n\nFather's name: Sunny Kumar");
        Storage::disk('local')->put('documents/other.txt', 'Other user content');

        $oldChunk = DocumentChunk::create([
            'document_id' => $document->id,
            'chunk_index' => 0,
            'content' => 'Old chunk that must be removed',
        ]);

        $otherChunk = DocumentChunk::create([
            'document_id' => $otherDocument->id,
            'chunk_index' => 0,
            'content' => 'Other user chunk must remain',
        ]);

        $documentService = Mockery::mock(DocumentService::class);
        $documentService->shouldReceive('extractText')
            ->once()
            ->withArgs(fn (string $path, ?string $mimeType) => str_ends_with($path, 'documents/student.txt') && $mimeType === 'text/plain')
            ->andReturn("Roll number: 1018640148\n\nFather's name: Sunny Kumar");
        $this->instance(DocumentService::class, $documentService);

        $embeddingService = Mockery::mock(EmbeddingService::class);
        $embeddingService->shouldReceive('generate')
            ->once()
            ->with("Roll number: 1018640148\n\nFather's name: Sunny Kumar")
            ->andReturn([0.1, 0.2, 0.3]);
        $this->instance(EmbeddingService::class, $embeddingService);

        $qdrantService = Mockery::mock(QdrantService::class);
        $qdrantService->shouldReceive('ensurePayloadIndexes')->once();
        $qdrantService->shouldReceive('deleteByDocument')->once()->with($document->id);
        $qdrantService->shouldReceive('store')
            ->once()
            ->withArgs(fn (int $chunkId, array $vector, array $payload) => $chunkId !== $oldChunk->id
                && $vector === [0.1, 0.2, 0.3]
                && $payload['document_id'] === $document->id
                && $payload['user_id'] === $owner->id
                && $payload['content'] === "Roll number: 1018640148\n\nFather's name: Sunny Kumar");
        $this->instance(QdrantService::class, $qdrantService);

        $exitCode = Artisan::call('documents:reprocess', [
            'document' => $document->id,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'file_path' => 'documents/student.txt',
            'content' => "Roll number: 1018640148\n\nFather's name: Sunny Kumar",
            'status' => 'completed',
        ]);
        $this->assertDatabaseMissing('document_chunks', [
            'id' => $oldChunk->id,
        ]);
        $this->assertDatabaseHas('document_chunks', [
            'document_id' => $document->id,
            'content' => "Roll number: 1018640148\n\nFather's name: Sunny Kumar",
        ]);
        $this->assertDatabaseHas('document_chunks', [
            'id' => $otherChunk->id,
            'document_id' => $otherDocument->id,
        ]);
    }

    public function test_reprocess_user_filter_cannot_process_another_users_document(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Private Record',
            'file_path' => 'documents/private.txt',
            'mime_type' => 'text/plain',
            'content' => 'Private content',
            'status' => 'completed',
        ]);

        $documentService = Mockery::mock(DocumentService::class);
        $documentService->shouldReceive('extractText')->never();
        $this->instance(DocumentService::class, $documentService);

        $exitCode = Artisan::call('documents:reprocess', [
            'document' => $document->id,
            '--user' => $otherUser->id,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No documents matched', Artisan::output());
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'completed',
        ]);
    }
}