<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertOk();
    }
}