<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()
            ->where('username', 'owner')
            ->orWhereIn('email', ['owner@toko.com', 'owner@toko.local'])
            ->firstOrNew();

        $owner->fill([
            'name' => 'Owner Toko',
            'username' => 'owner',
            'email' => 'owner@toko.local',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'mode_app' => null,
            'email_verified_at' => now(),
        ]);

        $owner->save();
    }
}
