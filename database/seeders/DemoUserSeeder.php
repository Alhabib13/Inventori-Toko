<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Owner Sederhana',
                'store_name' => 'Toko Sederhana',
                'username' => 'owner_sederhana',
                'email' => 'owner_sederhana@demo.local',
                'role' => 'owner',
                'mode_app' => 'sederhana',
            ],
            [
                'name' => 'Kasir Sederhana',
                'store_name' => 'Toko Sederhana',
                'username' => 'kasir_sederhana',
                'email' => 'kasir_sederhana@demo.local',
                'role' => 'kasir',
                'mode_app' => 'sederhana',
            ],
            [
                'name' => 'Owner Lengkap',
                'store_name' => 'Toko Lengkap',
                'username' => 'owner_lengkap',
                'email' => 'owner_lengkap@demo.local',
                'role' => 'owner',
                'mode_app' => 'lengkap',
            ],
            [
                'name' => 'Kasir Lengkap',
                'store_name' => 'Toko Lengkap',
                'username' => 'kasir_lengkap',
                'email' => 'kasir_lengkap@demo.local',
                'role' => 'kasir',
                'mode_app' => 'lengkap',
            ],
            [
                'name' => 'Gudang Lengkap',
                'store_name' => 'Toko Lengkap',
                'username' => 'gudang_lengkap',
                'email' => 'gudang_lengkap@demo.local',
                'role' => 'gudang',
                'mode_app' => 'lengkap',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['username' => $user['username']],
                $user + [
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
