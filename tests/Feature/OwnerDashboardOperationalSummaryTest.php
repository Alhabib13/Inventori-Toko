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
        $product = $this->createProduct($owner->store_name, [
            'nama_produk' => 'Produk Sederhana',
            'stok' => 1,
            'stok_minimum' => 2,
        ]);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-DS-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now(),
            'total_item' => 2,
            'subtotal' => 24000,
            'total_bayar' => 24000,
            'nominal_bayar' => 24000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 2,
            'harga' => 12000,
            'subtotal' => 24000,
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
            ->assertSee('Estimasi Keuntungan')
            ->assertSee('Rp8.000')
            ->assertSee('Jumlah Produk')
            ->assertSee('Stok Menipis')
            ->assertSee('Prediksi Restock')
            ->assertSee('Tren Keuntungan 7 Hari')
            ->assertSee('30 Hari')
            ->assertSee('Prioritas Hari Ini')
            ->assertSee('Produk Sederhana')
            ->assertSee('Produk Stok Menipis')
            ->assertSee('Produk Baru')
            ->assertDontSee('User Operasional');
    }

    public function test_owner_lengkap_sees_monitoring_summary_and_forecast_on_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct($owner->store_name, [
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
            ->assertSee('Tren Keuntungan 7 Hari')
            ->assertSee('Insight Inventori')
            ->assertSee('Produk Lengkap')
            ->assertSee('Lihat Prediksi')
            ->assertDontSee('Produk Baru')
            ->assertDontSee('User Operasional');
    }

    public function test_owner_can_switch_dashboard_sales_trend_period_to_30_days(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-30-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now()->subDays(20),
            'total_item' => 1,
            'subtotal' => 50000,
            'total_bayar' => 50000,
            'nominal_bayar' => 50000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $product = $this->createProduct($owner->store_name, [
            'harga_beli' => 30000,
            'harga_jual' => 50000,
        ]);
        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga' => 50000,
            'subtotal' => 50000,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard.index', ['trend' => 30]))
            ->assertOk()
            ->assertSee('Tren Keuntungan 30 Hari')
            ->assertSee('Periode Dipilih')
            ->assertSee('30 Hari Terakhir')
            ->assertSee('Total Keuntungan')
            ->assertSee('Rp20.000');
    }

    public function test_dashboard_summary_excludes_cancelled_sales_and_purchases(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct($owner->store_name, [
            'nama_produk' => 'Produk Dashboard Valid',
            'harga_beli' => 15000,
            'harga_jual' => 20000,
            'stok' => 10,
        ]);

        $validTransaction = Transaction::create([
            'kode_transaksi' => 'TRX-DASH-VALID',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'nominal_bayar' => 20000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $validTransaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga' => 20000,
            'subtotal' => 20000,
        ]);

        $cancelledTransaction = Transaction::create([
            'kode_transaksi' => 'TRX-DASH-CANCEL',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 90000,
            'total_bayar' => 90000,
            'nominal_bayar' => 90000,
            'kembalian' => 0,
            'status' => 'dibatalkan',
        ]);
        $cancelledTransaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga' => 90000,
            'subtotal' => 90000,
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-DASH-VALID',
            'supplier_id' => $product->supplier_id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 30000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-DASH-CANCEL',
            'supplier_id' => $product->supplier_id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 70000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 70000,
            'status' => 'dibatalkan',
        ]);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Estimasi Keuntungan')
            ->assertSee('Rp5.000')
            ->assertSee('Total Pembelian')
            ->assertSee('Rp30.000')
            ->assertDontSee('Rp75.000')
            ->assertDontSee('Rp90.000')
            ->assertDontSee('Rp70.000');
    }

    private function createProduct(string $storeName, array $attributes = []): Product
    {
        $category = Category::create([
            'nama_kategori' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'store_name' => $storeName,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => fake()->unique()->company(),
            'nama_kontak' => fake()->name(),
            'telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'store_name' => $storeName,
            'is_active' => true,
        ]);

        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'store_name' => $storeName,
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
