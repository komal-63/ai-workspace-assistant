<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_password_toggle_markup_is_present_and_non_submit(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('data-password-toggle="password"', false);
        $response->assertSee('type="button"', false);
        $response->assertSee('aria-label="Show password"', false);
        $response->assertSee('aria-pressed="false"', false);
    }

    public function test_registration_password_toggle_markup_is_present_for_confirmation_field(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('data-password-toggle="password_confirmation"', false);
        $response->assertSee('type="button"', false);
    }
}
