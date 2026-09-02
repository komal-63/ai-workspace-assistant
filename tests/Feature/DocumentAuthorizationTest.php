<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_document(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Private Document',
            'file_path' => 'documents/private.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'Private document content',
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->get("/documents/{$document->id}/view");

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_document(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Private Document',
            'file_path' => 'documents/private.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'Private document content',
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete("/documents/{$document->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
        ]);
    }
}