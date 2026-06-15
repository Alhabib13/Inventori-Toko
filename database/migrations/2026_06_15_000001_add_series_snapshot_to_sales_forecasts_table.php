<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_forecasts', function (Blueprint $table): void {
            $table->json('series_snapshot')->nullable()->after('selisih_prediksi');
        });
    }

    public function down(): void
    {
        Schema::table('sales_forecasts', function (Blueprint $table): void {
            $table->dropColumn('series_snapshot');
        });
    }
};
