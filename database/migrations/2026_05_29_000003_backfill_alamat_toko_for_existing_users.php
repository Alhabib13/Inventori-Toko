<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultAddresses = [
            'Toko Sederhana' => 'Jl. Melati No. 10, Surabaya',
            'Toko Lengkap' => 'Jl. Kenanga No. 15, Bandung',
        ];

        foreach ($defaultAddresses as $storeName => $address) {
            DB::table('users')
                ->where('store_name', $storeName)
                ->whereNull('alamat_toko')
                ->update(['alamat_toko' => $address]);
        }
    }

    public function down(): void
    {
        $defaultAddresses = [
            'Toko Sederhana' => 'Jl. Melati No. 10, Surabaya',
            'Toko Lengkap' => 'Jl. Kenanga No. 15, Bandung',
        ];

        DB::table('users')
            ->whereIn('alamat_toko', array_values($defaultAddresses))
            ->update(['alamat_toko' => null]);
    }
};
