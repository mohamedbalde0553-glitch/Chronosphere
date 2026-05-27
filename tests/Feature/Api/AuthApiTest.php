<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('correct'),
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_inactive_account(): void
    {
        User::factory()->create([
            'email'     => 'inactive@example.com',
            'password'  => bcrypt('password'),
            'is_active' => false,
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password',
        ])
            ->assertStatus(403);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Déconnecté.']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/employees')
            ->assertStatus(401);
    }
}
