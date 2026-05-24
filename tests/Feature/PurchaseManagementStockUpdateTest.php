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
        $supplier = $this->createSupplier();
        $firstProduct = $this->createProduct(['stok' => 4, 'harga_beli' => 10000]);
        $secondProduct = $this->createProduct(['stok' => 2, 'harga_beli' => 7000]);

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
        $supplier = $this->createSupplier();
        $product = $this->createProduct(['stok' => 1]);

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
        $supplier = $this->createSupplier();
        $product = $this->createProduct();

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
        $supplier = $this->createSupplier();

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
        $supplier = $this->createSupplier();
        $product = $this->createProduct(['nama_produk' => 'Produk Pembelian Detail']);

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

    private function createSupplier(): Supplier
    {
        return Supplier::create([
            'nama_supplier' => fake()->unique()->company(),
            'nama_kontak' => fake()->name(),
            'telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'is_active' => true,
        ]);
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'nama_kategori' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'is_active' => true,
        ]);

        $supplier = $this->createSupplier();

        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
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
