<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_shows_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('AI Workspace Assistant');
        $response->assertSee('Login');
        $response->assertSee('Get Started');
        $response->assertSee('Create Account');
    }

    public function test_authenticated_user_root_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_streaming_endpoint_requires_authentication(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Test conversation',
        ]);

        $this->post(route('messages.stream', $conversation), [
            'content' => 'Explain dependency injection',
        ])->assertRedirect(route('login'));
    }

    public function test_user_cannot_stream_into_another_users_conversation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $owner->id,
            'title' => 'Owner conversation',
        ]);

        $this->actingAs($otherUser)
            ->postJson(route('messages.stream', $conversation), [
                'content' => 'Hack the conversation',
            ])
            ->assertForbidden();
    }
}
