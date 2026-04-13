<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_forecasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->unsignedTinyInteger('panjang_jendela')->default(3);
            $table->decimal('nilai_moving_average', 15, 2)->default(0);
            $table->integer('prediksi_stok')->default(0);
            $table->integer('stok_aktual')->default(0);
            $table->integer('selisih_prediksi')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_forecasts');
    }
};
