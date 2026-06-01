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
        $product = $this->createProduct($owner->store_name, [
            'nama_produk' => 'Produk Laporan',
            'stok' => 12,
            'harga_beli' => 15000,
            'harga_jual' => 20000,
        ]);
        $supplier = Supplier::findOrFail($product->supplier_id);

        $transactionInPeriod = Transaction::create([
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
        $transactionInPeriod->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 2,
            'harga' => 20000,
            'subtotal' => 40000,
        ]);

        $oldTransaction = Transaction::create([
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
        $oldTransaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga' => 20000,
            'subtotal' => 20000,
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

    public function test_owner_can_export_and_print_main_reports(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct($owner->store_name, [
            'nama_produk' => 'Produk Export',
            'stok' => 10,
            'harga_beli' => 12000,
        ]);
        $supplier = Supplier::findOrFail($product->supplier_id);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-EXPORT-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now()->subDay(),
            'total_item' => 3,
            'subtotal' => 90000,
            'total_bayar' => 90000,
            'nominal_bayar' => 100000,
            'kembalian' => 10000,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);
        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 3,
            'harga' => 30000,
            'subtotal' => 90000,
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-EXPORT-001',
            'supplier_id' => $supplier->id,
            'user_id' => $owner->id,
            'tanggal_pembelian' => now()->subDay(),
            'subtotal' => 50000,
            'diskon' => 5000,
            'ongkir' => 0,
            'total_bayar' => 45000,
            'status' => 'selesai',
        ]);

        $salesExport = $this->actingAs($owner)
            ->get('/reports/export/sales?period=30_hari')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $salesCsv = $salesExport->streamedContent();
        $this->assertStringContainsString('TRX-EXPORT-001', $salesCsv);
        $this->assertStringContainsString('Keuntungan', $salesCsv);

        $purchaseExport = $this->actingAs($owner)
            ->get('/reports/export/purchases?period=30_hari')
            ->assertOk();
        $this->assertStringContainsString('PO-EXPORT-001', $purchaseExport->streamedContent());

        $stockExport = $this->actingAs($owner)
            ->get('/reports/export/stock?period=30_hari')
            ->assertOk();
        $this->assertStringContainsString('Produk Export', $stockExport->streamedContent());

        $profitExport = $this->actingAs($owner)
            ->get('/reports/export/profit?period=30_hari')
            ->assertOk();
        $this->assertStringContainsString('Omzet Penjualan', $profitExport->streamedContent());

        $this->actingAs($owner)
            ->get('/reports/print/sales?period=30_hari')
            ->assertOk()
            ->assertSee('Laporan Penjualan')
            ->assertSee('TRX-EXPORT-001');
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

        $this->actingAs($kasir)
            ->get('/reports/export/sales')
            ->assertForbidden();
    }

    public function test_gudang_lengkap_can_view_stock_and_purchase_reports_only(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct($gudang->store_name, [
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

        $warehousePurchaseExport = $this->actingAs($gudang)
            ->get('/reports/export/purchases?period=30_hari')
            ->assertOk();
        $this->assertStringContainsString('PO-GDG-001', $warehousePurchaseExport->streamedContent());

        $this->actingAs($gudang)
            ->get('/reports/print/stock?period=30_hari')
            ->assertOk()
            ->assertSee('Laporan Stok')
            ->assertSee('Produk Gudang');

        $this->actingAs($gudang)
            ->get('/reports/export/sales?period=30_hari')
            ->assertForbidden();
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
            'harga_beli' => 10000,
            'harga_jual' => 20000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }
}
