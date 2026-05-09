<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sembako',
            'Minuman',
            'Snack',
        ];

        foreach ($categories as $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'nama_kategori' => $name,
                    'deskripsi' => 'Data demo kategori '.$name,
                    'is_active' => true,
                ],
            );
        }

        $suppliers = [
            [
                'nama_supplier' => 'PT Sumber Rejeki',
                'nama_kontak' => 'Budi',
                'telepon' => '081234567890',
                'alamat' => 'Jl. Raya No. 1',
            ],
            [
                'nama_supplier' => 'CV Maju Jaya',
                'nama_kontak' => 'Andi',
                'telepon' => '082345678901',
                'alamat' => 'Jl. Melati No. 2',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['nama_supplier' => $supplier['nama_supplier']],
                $supplier + ['is_active' => true],
            );
        }
    }
}
