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

class StockManagementMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_incoming_stock_and_product_stock_increases(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct(['stok' => 10]);

        $this->actingAs($owner)
            ->post('/stocks', [
                'product_id' => $product->id,
                'qty' => 5,
                'catatan' => 'Restok awal',
            ])
            ->assertRedirect(route('stocks.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 15,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 5,
            'stok_sebelum' => 10,
            'stok_sesudah' => 15,
            'referensi_tipe' => 'manual',
        ]);
    }

    public function test_gudang_lengkap_can_record_incoming_stock(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct(['stok' => 3]);

        $this->actingAs($gudang)
            ->post('/stocks', [
                'product_id' => $product->id,
                'qty' => 2,
            ])
            ->assertRedirect(route('stocks.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 5,
        ]);
    }

    public function test_kasir_can_only_view_stock_read_only(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct(['stok' => 8]);

        $this->actingAs($kasir)
            ->get('/stok')
            ->assertOk()
            ->assertSee($product->nama_produk)
            ->assertDontSee('Catat Stok Masuk');

        $this->actingAs($kasir)->get('/stocks/create')->assertForbidden();
        $this->actingAs($kasir)->post('/stocks', [
            'product_id' => $product->id,
            'qty' => 1,
        ])->assertForbidden();
    }

    public function test_sales_transaction_reduces_stock_and_creates_stock_movement_records(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct([
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

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 7,
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => 3,
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

    public function test_transaction_cannot_reduce_stock_below_zero(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct(['stok' => 2]);

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

        $this->assertEquals(0, StockMovement::query()->count());
        $this->assertEquals(0, Transaction::query()->count());
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
            'harga_jual' => 15000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }
}
