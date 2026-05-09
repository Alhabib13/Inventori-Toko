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

    public function test_owner_lengkap_can_create_purchase_with_multiple_items_and_stock_updates(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier();
        $firstProduct = $this->createProduct(['stok' => 4, 'harga_beli' => 10000]);
        $secondProduct = $this->createProduct(['stok' => 2, 'harga_beli' => 7000]);

        $response = $this->actingAs($owner)->post('/purchases', [
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
            'user_id' => $owner->id,
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
            'user_id' => $owner->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 3,
            'stok_sebelum' => 4,
            'stok_sesudah' => 7,
            'referensi_tipe' => 'purchase',
            'referensi_id' => $purchase->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $secondProduct->id,
            'user_id' => $owner->id,
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
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier();
        $product = $this->createProduct();

        $this->actingAs($owner)
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
