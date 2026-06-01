<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('purchase_items', 'harga_beli_sebelum')) {
            return;
        }

        Schema::table('purchase_items', function (Blueprint $table): void {
            $table->decimal('harga_beli_sebelum', 15, 2)->nullable()->after('harga_beli');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('purchase_items', 'harga_beli_sebelum')) {
            return;
        }

        Schema::table('purchase_items', function (Blueprint $table): void {
            $table->dropColumn('harga_beli_sebelum');
        });
    }
};
