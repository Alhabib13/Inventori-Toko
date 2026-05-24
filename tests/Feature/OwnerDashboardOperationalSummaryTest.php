<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardOperationalSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sederhana_sees_operational_summary_on_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct([
            'nama_produk' => 'Produk Sederhana',
            'stok' => 1,
            'stok_minimum' => 2,
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-DS-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now(),
            'total_item' => 2,
            'subtotal' => 40000,
            'total_bayar' => 40000,
            'nominal_bayar' => 40000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'periode_awal' => now()->subMonths(2)->startOfMonth(),
            'periode_akhir' => now()->endOfMonth(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 5.00,
            'prediksi_stok' => 5,
            'stok_aktual' => 1,
            'selisih_prediksi' => 4,
            'catatan' => 'Restock segera.',
        ]);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan Penjualan')
            ->assertSee('Jumlah Produk')
            ->assertSee('Stok Menipis')
            ->assertSee('Prediksi Restock')
            ->assertSee('Produk Sederhana')
            ->assertSee('Produk Stok Menipis')
            ->assertSee('Produk Baru');
    }

    public function test_owner_lengkap_sees_monitoring_summary_and_forecast_on_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct([
            'nama_produk' => 'Produk Lengkap',
            'stok' => 3,
            'stok_minimum' => 5,
            'harga_beli' => 10000,
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-DASH-001',
            'supplier_id' => $product->supplier_id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 30000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'periode_awal' => now()->subMonths(2)->startOfMonth(),
            'periode_akhir' => now()->endOfMonth(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 6.00,
            'prediksi_stok' => 6,
            'stok_aktual' => 3,
            'selisih_prediksi' => 3,
            'catatan' => 'Monitoring owner lengkap.',
        ]);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Pembelian')
            ->assertSee('Nilai Stok')
            ->assertSee('Supplier Aktif')
            ->assertSee('Prediksi Restock')
            ->assertSee('Produk Lengkap')
            ->assertSee('Lihat Prediksi')
            ->assertDontSee('Produk Baru');
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'nama_kategori' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => fake()->unique()->company(),
            'nama_kontak' => fake()->name(),
            'telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'is_active' => true,
        ]);

        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'kode_produk' => 'PRD-'.fake()->unique()->numerify('####'),
            'nama_produk' => 'Produk '.fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'satuan' => 'pcs',
            'harga_beli' => 8000,
            'harga_jual' => 12000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }
}
