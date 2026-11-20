<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@loadora.test',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@loadora.test',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify activity log was recorded
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_staff_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->staff()->create([
            'email' => 'staff@loadora.test',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@loadora.test',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@loadora.test',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@loadora.test',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_invalid_password_fails_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@loadora.test',
            'password' => Hash::make('CorrectPassword'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@loadora.test',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'auth.logout',
        ]);
    }
}
