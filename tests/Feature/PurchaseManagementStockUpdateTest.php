<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseManagementStockUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_gudang_lengkap_can_create_purchase_with_multiple_items_and_stock_updates(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $firstProduct = $this->createProduct($gudang->store_name, ['stok' => 4, 'harga_beli' => 10000]);
        $secondProduct = $this->createProduct($gudang->store_name, ['stok' => 2, 'harga_beli' => 7000]);

        $response = $this->actingAs($gudang)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $firstProduct->id,
                    'qty' => 3,
                    'harga_beli' => 11000,
                ],
                [
                    'product_id' => $secondProduct->id,
                    'qty' => 5,
                    'harga_beli' => 8000,
                ],
            ],
            'diskon' => 2000,
            'ongkir' => 5000,
            'catatan' => 'Pembelian mingguan',
        ]);

        $purchase = Purchase::query()->first();

        $response->assertRedirect(route('purchases.show', $purchase));

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'subtotal' => 73000,
            'diskon' => 2000,
            'ongkir' => 5000,
            'total_bayar' => 76000,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $firstProduct->id,
            'qty' => 3,
            'harga_beli' => 11000,
            'subtotal' => 33000,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $secondProduct->id,
            'qty' => 5,
            'harga_beli' => 8000,
            'subtotal' => 40000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $firstProduct->id,
            'stok' => 7,
            'harga_beli' => 11000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $secondProduct->id,
            'stok' => 7,
            'harga_beli' => 8000,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $firstProduct->id,
            'user_id' => $gudang->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 3,
            'stok_sebelum' => 4,
            'stok_sesudah' => 7,
            'referensi_tipe' => 'purchase',
            'referensi_id' => $purchase->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $secondProduct->id,
            'user_id' => $gudang->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 5,
            'stok_sebelum' => 2,
            'stok_sesudah' => 7,
            'referensi_tipe' => 'purchase',
            'referensi_id' => $purchase->id,
        ]);
    }

    public function test_gudang_lengkap_can_create_purchase(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, ['stok' => 1]);

        $this->actingAs($gudang)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'harga_beli' => 12000,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 3,
        ]);
    }

    public function test_kasir_cannot_access_purchase_routes(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($kasir)->get('/purchases')->assertForbidden();
        $this->actingAs($kasir)->get('/purchases/create')->assertForbidden();
        $this->actingAs($kasir)->post('/purchases', [])->assertForbidden();
    }

    public function test_purchase_requires_at_least_one_item_with_quantity(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name);

        $this->actingAs($gudang)
            ->from('/purchases/create')
            ->post('/purchases', [
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 0,
                        'harga_beli' => 10000,
                    ],
                ],
            ])
            ->assertRedirect('/purchases/create')
            ->assertSessionHasErrors('items');

        $this->assertEquals(0, Purchase::query()->count());
        $this->assertEquals(0, StockMovement::query()->count());
    }

    public function test_owner_lengkap_cannot_access_purchase_routes(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Monitoring',
        ]);

        $purchaseRecorder = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Monitoring',
        ]);
        $supplier = $this->createSupplier($owner->store_name);

        $purchase = Purchase::create([
            'kode_pembelian' => 'PO-MONITOR-001',
            'supplier_id' => $supplier->id,
            'user_id' => $purchaseRecorder->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 30000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)->get('/purchases')->assertOk();
        $this->actingAs($owner)->get(route('purchases.show', $purchase))->assertOk();
        $this->actingAs($owner)->get('/purchases/create')->assertForbidden();
        $this->actingAs($owner)->post('/purchases', [])->assertForbidden();
    }

    public function test_purchase_history_supports_period_filter_and_detail_content(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Gudang',
            'name' => 'Gudang Utama',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, ['nama_produk' => 'Produk Pembelian Detail']);

        $recentPurchase = Purchase::create([
            'kode_pembelian' => 'PO-RECENT-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now()->subDays(2),
            'subtotal' => 24000,
            'diskon' => 1000,
            'ongkir' => 500,
            'total_bayar' => 23500,
            'status' => 'selesai',
        ]);

        $recentPurchase->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => 'Produk Pembelian Detail',
            'qty' => 2,
            'harga_beli' => 12000,
            'subtotal' => 24000,
        ]);

        Purchase::create([
            'kode_pembelian' => 'PO-OLD-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now()->subDays(20),
            'subtotal' => 10000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 10000,
            'status' => 'selesai',
        ]);

        $this->actingAs($gudang)
            ->get(route('purchases.index', [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('PO-RECENT-001')
            ->assertDontSee('PO-OLD-001');

        $this->actingAs($gudang)
            ->get(route('purchases.show', $recentPurchase))
            ->assertOk()
            ->assertSee($supplier->nama_supplier)
            ->assertSee('Gudang Utama')
            ->assertSee('Produk Pembelian Detail')
            ->assertSee('Rp24.000')
            ->assertSee('Rp23.500');
    }

    public function test_gudang_can_cancel_purchase_and_reduce_stock_back(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Gudang',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, [
            'stok' => 9,
            'nama_produk' => 'Produk Cancel Purchase',
        ]);

        $purchase = Purchase::create([
            'kode_pembelian' => 'PO-CANCEL-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 30000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        $purchase->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 4,
            'harga_beli' => 7500,
            'subtotal' => 30000,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $gudang->id,
            'referensi_tipe' => 'purchase',
            'referensi_id' => $purchase->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 4,
            'stok_sebelum' => 5,
            'stok_sesudah' => 9,
            'catatan' => 'Pembelian '.$purchase->kode_pembelian,
            'tanggal_pergerakan' => now(),
        ]);

        $this->actingAs($gudang)
            ->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.show', $purchase));

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'dibatalkan',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 5,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $gudang->id,
            'referensi_tipe' => 'purchase_cancellation',
            'referensi_id' => $purchase->id,
            'jenis_pergerakan' => 'keluar',
            'qty' => 4,
            'stok_sebelum' => 9,
            'stok_sesudah' => 5,
        ]);
    }

    public function test_purchase_cancellation_restores_previous_product_buy_price(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Gudang',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, [
            'stok' => 5,
            'harga_beli' => 10000,
        ]);

        $this->actingAs($gudang)
            ->post('/purchases', [
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 4,
                        'harga_beli' => 12000,
                    ],
                ],
                'diskon' => 0,
                'ongkir' => 0,
            ]);

        $purchase = Purchase::query()->firstOrFail();

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'harga_beli' => 12000,
            'harga_beli_sebelum' => 10000,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 9,
            'harga_beli' => 12000,
        ]);

        $this->actingAs($gudang)
            ->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.show', $purchase));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 5,
            'harga_beli' => 10000,
        ]);
    }

    public function test_purchase_cancellation_does_not_override_newer_product_buy_price(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Gudang',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, [
            'stok' => 5,
            'harga_beli' => 10000,
        ]);

        $this->actingAs($gudang)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 4,
                    'harga_beli' => 12000,
                ],
            ],
            'diskon' => 0,
            'ongkir' => 0,
        ]);
        $firstPurchase = Purchase::query()->firstOrFail();

        $this->actingAs($gudang)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                    'harga_beli' => 14000,
                ],
            ],
            'diskon' => 0,
            'ongkir' => 0,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 10,
            'harga_beli' => 14000,
        ]);

        $this->actingAs($gudang)
            ->delete(route('purchases.destroy', $firstPurchase))
            ->assertRedirect(route('purchases.show', $firstPurchase));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 6,
            'harga_beli' => 14000,
        ]);
    }

    public function test_purchase_cancellation_is_rejected_when_stock_rollback_would_be_invalid(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Gudang',
        ]);
        $supplier = $this->createSupplier($gudang->store_name);
        $product = $this->createProduct($gudang->store_name, [
            'stok' => 2,
            'nama_produk' => 'Produk Rollback Invalid',
        ]);

        $purchase = Purchase::create([
            'kode_pembelian' => 'PO-ROLLBACK-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 30000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 30000,
            'status' => 'selesai',
        ]);

        $purchase->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 4,
            'harga_beli' => 7500,
            'subtotal' => 30000,
        ]);

        $this->actingAs($gudang)
            ->from(route('purchases.show', $purchase))
            ->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.show', $purchase))
            ->assertSessionHasErrors('items');

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 2,
        ]);

        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'referensi_tipe' => 'purchase_cancellation',
            'referensi_id' => $purchase->id,
        ]);
    }

    public function test_owner_cannot_cancel_purchase(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Monitoring',
        ]);
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Monitoring',
        ]);
        $supplier = $this->createSupplier($owner->store_name);

        $purchase = Purchase::create([
            'kode_pembelian' => 'PO-OWNER-FORBIDDEN',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 10000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 10000,
            'status' => 'selesai',
        ]);

        $this->actingAs($owner)
            ->delete(route('purchases.destroy', $purchase))
            ->assertForbidden();
    }

    private function createSupplier(string $storeName): Supplier
    {
        return Supplier::create([
            'nama_supplier' => fake()->unique()->company(),
            'nama_kontak' => fake()->name(),
            'telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'store_name' => $storeName,
            'is_active' => true,
        ]);
    }

    private function createProduct(string $storeName, array $attributes = []): Product
    {
        $category = Category::create([
            'nama_kategori' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'store_name' => $storeName,
            'is_active' => true,
        ]);

        $supplier = $this->createSupplier($storeName);

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
