<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY mode_app VARCHAR(50) NULL DEFAULT NULL');
        DB::table('users')
            ->where('mode_app', 'toko')
            ->update(['mode_app' => null]);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY mode_app VARCHAR(50) NOT NULL DEFAULT 'toko'");
    }
};
