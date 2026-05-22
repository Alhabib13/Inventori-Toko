<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesForecastMovingAverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sederhana_can_generate_sales_forecast_from_recent_sales_history(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct([
            'nama_produk' => 'Produk Forecast',
            'stok' => 4,
            'satuan' => 'pcs',
        ]);

        $this->createTransactionWithItem($owner, $product, now()->subMonths(2)->startOfMonth()->addDays(2), 12);
        $this->createTransactionWithItem($owner, $product, now()->subMonth()->startOfMonth()->addDays(5), 6);
        $this->createTransactionWithItem($owner, $product, now()->startOfMonth()->addDays(1), 9);

        $response = $this->actingAs($owner)->post('/forecasts', [
            'product_id' => $product->id,
            'periode_akhir' => now()->toDateString(),
            'panjang_jendela' => 3,
            'catatan' => '',
        ]);

        $forecast = SalesForecast::query()->first();

        $response
            ->assertRedirect(route('forecasts.show', $forecast))
            ->assertSessionHasNoErrors();

        $this->assertNotNull($forecast);
        $this->assertSame(9.00, (float) $forecast->nilai_moving_average);
        $this->assertSame(9, $forecast->prediksi_stok);
        $this->assertSame(4, $forecast->stok_aktual);
        $this->assertSame(5, $forecast->selisih_prediksi);

        $this->actingAs($owner)
            ->get('/forecasts')
            ->assertOk()
            ->assertSee('Produk Forecast')
            ->assertSee('9')
            ->assertSee('5');
    }

    public function test_gudang_lengkap_can_view_sales_forecast_list(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct(['nama_produk' => 'Produk Gudang Forecast']);

        SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $gudang->id,
            'periode_awal' => now()->subMonths(2)->startOfMonth(),
            'periode_akhir' => now()->endOfMonth(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 7.33,
            'prediksi_stok' => 8,
            'stok_aktual' => 3,
            'selisih_prediksi' => 5,
            'catatan' => 'Perlu restock.',
        ]);

        $this->actingAs($gudang)
            ->get('/forecasts')
            ->assertOk()
            ->assertSee('Produk Gudang Forecast')
            ->assertSee('8')
            ->assertSee('5');
    }

    public function test_owner_lengkap_can_only_view_sales_forecast_list(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $product = $this->createProduct(['nama_produk' => 'Produk Owner Lengkap']);

        $forecast = SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'periode_awal' => now()->subMonths(2)->startOfMonth(),
            'periode_akhir' => now()->endOfMonth(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 5.67,
            'prediksi_stok' => 6,
            'stok_aktual' => 9,
            'selisih_prediksi' => 0,
            'catatan' => 'Monitoring owner lengkap.',
        ]);

        $this->actingAs($owner)
            ->get('/forecasts')
            ->assertOk()
            ->assertSee('Produk Owner Lengkap')
            ->assertDontSee('Tambah Prediksi');

        $this->actingAs($owner)
            ->get(route('forecasts.show', $forecast))
            ->assertOk()
            ->assertSee('Produk Owner Lengkap')
            ->assertDontSee('Edit Prediksi')
            ->assertDontSee('Hapus');

        $this->actingAs($owner)->get('/forecasts/create')->assertForbidden();
        $this->actingAs($owner)->post('/forecasts', [])->assertForbidden();
        $this->actingAs($owner)->get(route('forecasts.edit', $forecast))->assertForbidden();
        $this->actingAs($owner)->delete(route('forecasts.destroy', $forecast))->assertForbidden();
    }

    public function test_kasir_cannot_access_sales_forecast_routes(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($kasir)->get('/forecasts')->assertForbidden();
        $this->actingAs($kasir)->get('/forecasts/create')->assertForbidden();
        $this->actingAs($kasir)->post('/forecasts', [])->assertForbidden();
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

    private function createTransactionWithItem(User $user, Product $product, \Carbon\Carbon $date, int $qty): void
    {
        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-'.fake()->unique()->numerify('#####'),
            'user_id' => $user->id,
            'tanggal_transaksi' => $date,
            'total_item' => $qty,
            'subtotal' => $qty * (float) $product->harga_jual,
            'total_bayar' => $qty * (float) $product->harga_jual,
            'nominal_bayar' => $qty * (float) $product->harga_jual,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => $qty,
            'harga' => $product->harga_jual,
            'subtotal' => $qty * (float) $product->harga_jual,
        ]);
    }
}
