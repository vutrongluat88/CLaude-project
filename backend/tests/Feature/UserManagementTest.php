<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guests_cannot_touch_the_user_endpoints(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/users')->assertUnauthorized();
        $this->postJson('/api/users', [])->assertUnauthorized();
        $this->putJson("/api/users/{$user->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/users/{$user->id}")->assertUnauthorized();
    }

    public function test_users_are_listed_paginated_and_newest_first(): void
    {
        $admin = $this->admin();
        User::factory()->count(20)->create();

        $response = $this->actingAs($admin)->getJson('/api/users?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 21)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email', 'role', 'created_at']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->assertArrayNotHasKey('password', $response->json('data.0'));
    }

    public function test_users_can_be_searched_by_name_or_email(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Nguyen Van A', 'email' => 'a@example.com']);
        User::factory()->create(['name' => 'Tran Thi B', 'email' => 'b@example.com']);

        $this->actingAs($admin)->getJson('/api/users?search=Tran')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Tran Thi B');

        $this->actingAs($admin)->getJson('/api/users?search=a@example.com')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'a@example.com');
    }

    public function test_a_user_can_be_created(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Vu Trong Luat',
            'email' => 'luat@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Vu Trong Luat')
            ->assertJsonPath('data.role', 'admin');

        $created = User::where('email', 'luat@example.com')->sole();
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_creating_a_user_validates_its_input(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->postJson('/api/users', [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'short',
            'role' => 'superhero',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_a_single_user_can_be_fetched(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_a_user_can_be_updated_without_changing_the_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Old Name', 'password' => 'original']);

        $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => 'user',
        ])->assertOk()->assertJsonPath('data.name', 'New Name');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertTrue(Hash::check('original', $user->password));
    }

    public function test_updating_a_user_can_set_a_new_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'password' => 'brand-new-pass',
        ])->assertOk();

        $this->assertTrue(Hash::check('brand-new-pass', $user->refresh()->password));
    }

    public function test_a_user_keeps_its_own_email_on_update(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['email' => 'keep@example.com']);

        $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => 'Same Email',
            'email' => 'keep@example.com',
            'role' => 'user',
        ])->assertOk();
    }

    public function test_a_user_cannot_take_an_email_already_in_use(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => $user->name,
            'email' => 'taken@example.com',
            'role' => 'user',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_be_deleted(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_a_user_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->deleteJson("/api/users/{$admin->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_dashboard_stats_are_returned(): void
    {
        $admin = $this->admin();
        User::factory()->count(3)->create(['role' => 'user']);
        User::factory()->create(['role' => 'admin', 'created_at' => now()->subMonths(2)]);

        $this->actingAs($admin)->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('data.total_users', 5)
            ->assertJsonPath('data.admins', 2)
            ->assertJsonStructure(['data' => ['total_users', 'admins', 'new_this_month', 'verified']]);
    }
}
