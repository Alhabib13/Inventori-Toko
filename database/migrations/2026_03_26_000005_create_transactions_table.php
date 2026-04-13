<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('tanggal_transaksi');
            $table->integer('total_item')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->decimal('nominal_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->string('metode_pembayaran', 50)->nullable();
            $table->string('status', 50)->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
