<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_admin_from_config_only_once(): void
    {
        config(['admin.name' => 'Boss', 'admin.email' => 'boss@example.com', 'admin.password' => 'first-secret']);
        $this->seed(AdminUserSeeder::class);

        // A later deploy must not reset a password the admin has since changed.
        config(['admin.password' => 'second-secret']);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $admin = User::firstWhere('email', 'boss@example.com');
        $this->assertSame('admin', $admin->role);
        $this->assertSame('Boss', $admin->name);
        $this->assertTrue(Hash::check('first-secret', $admin->password));
    }

    public function test_it_does_nothing_without_credentials(): void
    {
        config(['admin.email' => null, 'admin.password' => null]);

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }
}
