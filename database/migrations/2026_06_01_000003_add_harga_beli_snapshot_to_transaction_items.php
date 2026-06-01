<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transaction_items', 'harga_beli')) {
            Schema::table('transaction_items', function (Blueprint $table): void {
                $table->decimal('harga_beli', 15, 2)->nullable()->after('harga');
            });
        }

        DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereNull('transaction_items.harga_beli')
            ->orderBy('transaction_items.id')
            ->select(['transaction_items.id', 'products.harga_beli'])
            ->chunkById(100, function ($items): void {
                foreach ($items as $item) {
                    DB::table('transaction_items')
                        ->where('id', $item->id)
                        ->update(['harga_beli' => $item->harga_beli]);
                }
            }, 'transaction_items.id', 'id');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('transaction_items', 'harga_beli')) {
            return;
        }

        Schema::table('transaction_items', function (Blueprint $table): void {
            $table->dropColumn('harga_beli');
        });
    }
};
