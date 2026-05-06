<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mode_app', 50)->nullable()->default(null)->change();
        });

        DB::table('users')
            ->where('mode_app', 'toko')
            ->update(['mode_app' => null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mode_app', 50)->nullable(false)->default('toko')->change();
        });
    }
};

