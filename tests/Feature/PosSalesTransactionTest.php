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
