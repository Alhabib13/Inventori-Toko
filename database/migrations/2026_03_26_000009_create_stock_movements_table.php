<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('referensi_tipe', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->enum('jenis_pergerakan', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('qty');
            $table->integer('stok_sebelum')->default(0);
            $table->integer('stok_sesudah')->default(0);
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_pergerakan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
