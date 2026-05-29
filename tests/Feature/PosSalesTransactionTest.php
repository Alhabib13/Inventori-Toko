<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSalesTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_create_sales_transaction_from_pos(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct($kasir->store_name, [
            'stok' => 10,
            'harga_jual' => 12000,
        ]);

        $response = $this->actingAs($kasir)
            ->post('/transactions', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 3,
                    ],
                ],
                'nominal_bayar' => 40000,
                'metode_pembayaran' => 'tunai',
            ]);

        $transaction = Transaction::query()->first();

        $response->assertRedirect(route('transactions.show', $transaction));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $kasir->id,
            'total_item' => 3,
            'subtotal' => 36000,
            'total_bayar' => 36000,
            'nominal_bayar' => 40000,
            'kembalian' => 4000,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => 3,
            'harga' => 12000,
            'subtotal' => 36000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $kasir->id,
            'jenis_pergerakan' => 'keluar',
            'qty' => 3,
            'stok_sebelum' => 10,
            'stok_sesudah' => 7,
            'referensi_tipe' => 'transaction',
            'referensi_id' => $transaction->id,
        ]);
    }

    public function test_transaction_fails_when_stock_is_insufficient(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct($kasir->store_name, ['stok' => 2]);

        $this->actingAs($kasir)
            ->from('/pos')
            ->post('/transactions', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 5,
                    ],
                ],
            ])
            ->assertRedirect('/pos')
            ->assertSessionHasErrors('items');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 2,
        ]);

        $this->assertEquals(0, Transaction::query()->count());
        $this->assertEquals(0, StockMovement::query()->count());
    }

    public function test_owner_can_access_pos_in_selected_mode(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)->get('/pos')->assertOk();
        $this->actingAs($owner)->get('/transactions')->assertOk();
    }

    public function test_kasir_pos_shows_active_pos_summary(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->createProduct($kasir->store_name, [
            'nama_produk' => 'Produk Aman',
            'stok' => 10,
            'stok_minimum' => 2,
        ]);
        $this->createProduct($kasir->store_name, [
            'nama_produk' => 'Produk Menipis',
            'stok' => 2,
            'stok_minimum' => 2,
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-POS-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now()->subHour(),
            'total_item' => 1,
            'subtotal' => 15000,
            'total_bayar' => 15000,
            'nominal_bayar' => 15000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($kasir)
            ->get('/pos')
            ->assertOk()
            ->assertSee('Produk Aktif')
            ->assertSee('Stok Rendah')
            ->assertSee('Transaksi Hari Ini')
            ->assertSee('2')
            ->assertSee('1');
    }

    public function test_gudang_cannot_access_pos_routes(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($gudang)->get('/pos')->assertForbidden();
        $this->actingAs($gudang)->get('/transactions')->assertForbidden();
        $this->actingAs($gudang)->post('/transactions', [])->assertForbidden();
    }

    public function test_kasir_only_sees_and_opens_their_own_transactions(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'name' => 'Kasir A',
        ]);
        $otherKasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'name' => 'Kasir B',
        ]);

        $ownTransaction = Transaction::create([
            'kode_transaksi' => 'TRX-OWN-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $otherTransaction = Transaction::create([
            'kode_transaksi' => 'TRX-OTHER-001',
            'user_id' => $otherKasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 15000,
            'total_bayar' => 15000,
            'nominal_bayar' => 15000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($kasir)
            ->get('/transactions')
            ->assertOk()
            ->assertSee('TRX-OWN-001')
            ->assertDontSee('TRX-OTHER-001');

        $this->actingAs($kasir)
            ->get(route('transactions.show', $ownTransaction))
            ->assertOk();

        $this->actingAs($kasir)
            ->get(route('transactions.show', $otherTransaction))
            ->assertForbidden();
    }

    public function test_kasir_can_search_transaction_history_by_code_and_product_name(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $productA = $this->createProduct($kasir->store_name, [
            'nama_produk' => 'Lampu Philips 12W',
        ]);
        $productB = $this->createProduct($kasir->store_name, [
            'nama_produk' => 'Kabel Supreme 2x1.5',
        ]);

        $transactionByCode = Transaction::create([
            'kode_transaksi' => 'TRX-CARI-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 12000,
            'total_bayar' => 12000,
            'nominal_bayar' => 12000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $transactionByCode->detailItem()->create([
            'product_id' => $productA->id,
            'nama_produk' => $productA->nama_produk,
            'qty' => 1,
            'harga' => 12000,
            'subtotal' => 12000,
        ]);

        $transactionByProduct = Transaction::create([
            'kode_transaksi' => 'TRX-KBL-002',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 2,
            'subtotal' => 30000,
            'total_bayar' => 30000,
            'nominal_bayar' => 30000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $transactionByProduct->detailItem()->create([
            'product_id' => $productB->id,
            'nama_produk' => $productB->nama_produk,
            'qty' => 2,
            'harga' => 15000,
            'subtotal' => 30000,
        ]);

        $this->actingAs($kasir)
            ->get('/transactions?search=TRX-CARI')
            ->assertOk()
            ->assertSee('TRX-CARI-001')
            ->assertDontSee('TRX-KBL-002');

        $this->actingAs($kasir)
            ->get('/transactions?search=Supreme')
            ->assertOk()
            ->assertSee('TRX-KBL-002')
            ->assertDontSee('TRX-CARI-001');
    }

    public function test_owner_can_filter_transaction_history_by_period_and_store_scope(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Utama',
        ]);
        $kasirStore = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Utama',
            'name' => 'Kasir Store',
        ]);
        $kasirOtherStore = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Lain',
            'name' => 'Kasir Toko Lain',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-MAY-001',
            'user_id' => $kasirStore->id,
            'tanggal_transaksi' => now()->subDays(2),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-OLD-001',
            'user_id' => $kasirStore->id,
            'tanggal_transaksi' => now()->subDays(25),
            'total_item' => 1,
            'subtotal' => 9000,
            'total_bayar' => 9000,
            'nominal_bayar' => 9000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-OTHER-STORE',
            'user_id' => $kasirOtherStore->id,
            'tanggal_transaksi' => now()->subDays(1),
            'total_item' => 1,
            'subtotal' => 12000,
            'total_bayar' => 12000,
            'nominal_bayar' => 12000,
            'kembalian' => 0,
            'metode_pembayaran' => 'transfer',
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)
            ->get(route('transactions.index', [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('TRX-MAY-001')
            ->assertDontSee('TRX-OLD-001')
            ->assertDontSee('TRX-OTHER-STORE');
    }

    public function test_kasir_sees_daily_operational_summary_on_transaction_history(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'name' => 'Kasir Ringkasan',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-TODAY-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now()->subHour(),
            'total_item' => 2,
            'subtotal' => 25000,
            'total_bayar' => 25000,
            'nominal_bayar' => 25000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-TODAY-002',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now()->subMinutes(20),
            'total_item' => 3,
            'subtotal' => 40000,
            'total_bayar' => 40000,
            'nominal_bayar' => 40000,
            'kembalian' => 0,
            'metode_pembayaran' => 'qris',
            'status' => 'selesai',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-TODAY-CANCEL',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now()->subMinutes(5),
            'total_item' => 4,
            'subtotal' => 100000,
            'total_bayar' => 100000,
            'nominal_bayar' => 100000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'dibatalkan',
        ]);

        Transaction::create([
            'kode_transaksi' => 'TRX-YESTERDAY-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now()->subDay(),
            'total_item' => 10,
            'subtotal' => 150000,
            'total_bayar' => 150000,
            'nominal_bayar' => 150000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        $this->actingAs($kasir)
            ->get('/transactions')
            ->assertOk()
            ->assertSee('Transaksi Hari Ini')
            ->assertSee('Total Penjualan Hari Ini')
            ->assertSee('Item Terjual Hari Ini')
            ->assertSee('Rp65.000')
            ->assertSee('1 transaksi dibatalkan hari ini.');
    }

    public function test_transaction_detail_shows_item_payment_method_and_cashier_information(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'name' => 'Kasir Detail',
        ]);
        $product = $this->createProduct($kasir->store_name, [
            'nama_produk' => 'Produk Detail',
            'stok' => 9,
            'harga_jual' => 14000,
        ]);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-DETAIL-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 2,
            'subtotal' => 28000,
            'diskon' => 1000,
            'pajak' => 500,
            'total_bayar' => 27500,
            'nominal_bayar' => 30000,
            'kembalian' => 2500,
            'metode_pembayaran' => 'qris',
            'status' => 'selesai',
        ]);

        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => 'Produk Detail',
            'qty' => 2,
            'harga' => 14000,
            'subtotal' => 28000,
        ]);

        $this->actingAs($kasir)
            ->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Kasir Detail')
            ->assertSee('Qris')
            ->assertSee('Produk Detail')
            ->assertSee('Rp28.000')
            ->assertSee('Rp27.500');
    }

    public function test_owner_cannot_open_transaction_from_other_store(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko A',
        ]);
        $kasirOtherStore = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko B',
        ]);

        $otherStoreTransaction = Transaction::create([
            'kode_transaksi' => 'TRX-B-001',
            'user_id' => $kasirOtherStore->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)
            ->get(route('transactions.show', $otherStoreTransaction))
            ->assertForbidden();
    }

    public function test_owner_can_cancel_transaction_and_restore_stock(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko A',
        ]);
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko A',
        ]);
        $product = $this->createProduct($kasir->store_name, [
            'stok' => 7,
            'nama_produk' => 'Produk Batal',
        ]);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-CANCEL-001',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 3,
            'subtotal' => 45000,
            'total_bayar' => 45000,
            'nominal_bayar' => 50000,
            'kembalian' => 5000,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 3,
            'harga' => 15000,
            'subtotal' => 45000,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $kasir->id,
            'referensi_tipe' => 'transaction',
            'referensi_id' => $transaction->id,
            'jenis_pergerakan' => 'keluar',
            'qty' => 3,
            'stok_sebelum' => 10,
            'stok_sesudah' => 7,
            'catatan' => 'Transaksi penjualan '.$transaction->kode_transaksi,
            'tanggal_pergerakan' => now(),
        ]);

        $this->actingAs($owner)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.show', $transaction));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'dibatalkan',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 10,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'referensi_tipe' => 'transaction_cancellation',
            'referensi_id' => $transaction->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 3,
            'stok_sebelum' => 7,
            'stok_sesudah' => 10,
        ]);
    }

    public function test_kasir_cannot_cancel_transaction(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-KASIR-CANCEL',
            'user_id' => $kasir->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($kasir)
            ->delete(route('transactions.destroy', $transaction))
            ->assertForbidden();
    }

    public function test_owner_cannot_cancel_transaction_from_other_store(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko A',
        ]);
        $kasirOtherStore = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko B',
        ]);
        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-BATAL-B-001',
            'user_id' => $kasirOtherStore->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)
            ->delete(route('transactions.destroy', $transaction))
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
            'harga_jual' => 15000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }
}
