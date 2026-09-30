<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'admin@example.com')
            ->assertJsonPath('data.id', $user->id);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_login_requires_an_email_and_a_password(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_the_authenticated_user_can_be_fetched(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonMissingPath('data.password');
    }

    public function test_a_guest_cannot_fetch_the_authenticated_user(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_a_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/logout')
            ->assertNoContent();

        // The session guard is what logout clears; the default guard is `sanctum`,
        // whose in-memory user was set by actingAs() and outlives the request.
        $this->assertGuest('web');
    }
}
