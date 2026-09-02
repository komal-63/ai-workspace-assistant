<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Services\DocumentService;
use App\Jobs\ProcessDocumentJob;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_file_type_cannot_be_uploaded(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create(
            'malicious.exe',
            100,
            'application/x-msdownload'
        );

        $response = $this
            ->actingAs($user)
            ->post('/documents', [
                'title' => 'Invalid Document',
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

public function test_valid_txt_file_can_be_uploaded(): void
{
    Storage::fake('local');
    Queue::fake();

    $this->mock(DocumentService::class, function ($mock) {
        $mock->shouldReceive('extractText')
            ->once()
            ->andReturn('Laravel testing document content');
    });

    $user = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'notes.txt',
        'Laravel testing document content'
    );

    $response = $this
        ->actingAs($user)
        ->post('/documents', [
            'title' => 'Laravel Notes',
            'file' => $file,
        ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('documents', [
        'user_id' => $user->id,
        'title' => 'Laravel Notes',
    ]);

    Queue::assertPushed(ProcessDocumentJob::class);
}
}