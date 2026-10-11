<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Production-safe seeder run on every deploy: no factories (faker is a dev
 * dependency), and an existing admin is left untouched so a password changed
 * in the app survives redeploys.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set, skipping admin seed.');

            return;
        }

        User::firstOrCreate(['email' => $email], [
            'name' => config('admin.name'),
            'password' => $password,
            'role' => 'admin',
        ]);
    }
}
