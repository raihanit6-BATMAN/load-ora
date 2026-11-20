<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Unauthenticated user is redirected to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Login page returns status 200.
     */
    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('LOADORA');
    }

    /**
     * Authenticated user is redirected to dashboard.
     */
    public function test_authenticated_user_redirected_to_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }
}

