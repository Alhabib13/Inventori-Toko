<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@inventori.test'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('admin12345'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]
        );
    }
}
