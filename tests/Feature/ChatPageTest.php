<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_page_has_loading_feedback_and_duplicate_submit_protection(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Test conversation',
        ]);

        $response = $this->actingAs($user)->get(route('messages.index', $conversation));

        $response->assertOk();
        $response->assertSee('placeholder="Ask anything..."', false);
        $response->assertSee('id="chatLoadingOverlay"', false);
        $response->assertSee('Generating answer...', false);
        $response->assertSee('role="status"', false);
        $response->assertSee('let isSubmitting = false;', false);
        $response->assertSee('if (isSubmitting || messageInput.value.trim().length === 0)', false);
        $response->assertSee('isSubmitting = true;', false);
    }
}