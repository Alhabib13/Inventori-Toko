<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessAndContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_sales_purchase_stock_and_profit_reports_by_period(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct([
            'nama_produk' => 'Produk Laporan',
            'stok' => 12,
            'harga_beli' => 10000,
        ]);
        $supplier = Supplier::findOrFail($product->supplier_id);

        Transaction::create([
            'kode_transaksi' => 'TRX-IN-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now()->subDays(2),
            'total_item' => 2,
            'subtotal' => 40000,
            'total_bayar' => 40000,
            'nominal_bayar' => 40000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-OUT-OLD',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now()->subDays(45),
            'total_item' => 1,
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'nominal_bayar' => 20000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-IN-001',
            'supplier_id' => $supplier->id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now()->subDays(1),
            'subtotal' => 25000,
            'diskon' => 0,
            'ongkir' => 5000,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-OLD-001',
            'supplier_id' => $supplier->id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now()->subDays(50),
            'subtotal' => 15000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 15000,
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)
            ->get('/reports?period=30_hari')
            ->assertOk()
            ->assertSee('TRX-IN-001')
            ->assertDontSee('TRX-OUT-OLD')
            ->assertSee('PO-IN-001')
            ->assertDontSee('PO-OLD-001')
            ->assertSee('Produk Laporan')
            ->assertSee('Rp40.000')
            ->assertSee('Rp30.000')
            ->assertSee('Rp10.000');
    }

    public function test_owner_can_change_report_period_filter(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->get('/reports?period=7_hari')
            ->assertOk()
            ->assertSee('7 hari terakhir');
    }

    public function test_kasir_cannot_access_owner_reports(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($kasir)
            ->get('/reports')
            ->assertForbidden();
    }

    public function test_gudang_lengkap_can_view_stock_and_purchase_reports_only(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct([
            'nama_produk' => 'Produk Gudang',
            'stok' => 8,
        ]);
        $supplier = Supplier::findOrFail($product->supplier_id);

        Purchase::create([
            'kode_pembelian' => 'PO-GDG-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now()->subDays(2),
            'subtotal' => 18000,
            'diskon' => 0,
            'ongkir' => 2000,
            'total_bayar' => 20000,
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-GDG-001',
            'user_id' => $gudang->id,
            'tanggal_transaksi' => now()->subDay(),
            'total_item' => 1,
            'subtotal' => 15000,
            'total_bayar' => 15000,
            'nominal_bayar' => 15000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($gudang)
            ->get('/reports?period=30_hari')
            ->assertOk()
            ->assertSee('Laporan Pembelian')
            ->assertSee('Laporan Stok')
            ->assertSee('PO-GDG-001')
            ->assertSee('Produk Gudang')
            ->assertDontSee('Laporan Penjualan')
            ->assertDontSee('Laba Rugi Sederhana')
            ->assertDontSee('TRX-GDG-001');
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
            'harga_beli' => 10000,
            'harga_jual' => 20000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }
}
