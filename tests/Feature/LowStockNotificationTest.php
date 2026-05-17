<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_only_low_stock_products_in_notifications(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $criticalProduct = $this->createProduct([
            'nama_produk' => 'Beras Kritis',
            'stok' => 2,
            'stok_minimum' => 2,
        ]);

        $safeProduct = $this->createProduct([
            'nama_produk' => 'Gula Aman',
            'stok' => 8,
            'stok_minimum' => 2,
        ]);

        $this->actingAs($owner)
            ->get(route('stocks.notifications'))
            ->assertOk()
            ->assertSee($criticalProduct->nama_produk)
            ->assertDontSee($safeProduct->nama_produk)
            ->assertSee('Daftar Stok Menipis');
    }

    public function test_gudang_lengkap_can_see_low_stock_notifications(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $criticalProduct = $this->createProduct([
            'nama_produk' => 'Minyak Kritis',
            'stok' => 1,
            'stok_minimum' => 3,
        ]);

        $this->actingAs($gudang)
            ->get(route('stocks.notifications'))
            ->assertOk()
            ->assertSee($criticalProduct->nama_produk);
    }

    public function test_kasir_cannot_access_low_stock_notifications(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($kasir)
            ->get(route('stocks.notifications'))
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
