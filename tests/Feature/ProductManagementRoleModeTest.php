<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_product_pages_and_create_product_with_required_data(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory();
        $supplier = $this->createSupplier();

        $this->actingAs($owner)->get('/products')->assertOk();
        $this->actingAs($owner)->get('/products/create')->assertOk();

        $this->actingAs($owner)
            ->post('/products', $this->validProductPayload($category, $supplier, [
                'nama_produk' => 'Beras Ramos 5kg',
            ]))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Beras Ramos 5kg',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'satuan' => 'pcs',
            'stok_minimum' => 5,
        ]);
    }

    public function test_gudang_lengkap_can_crud_products(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $category = $this->createCategory();
        $supplier = $this->createSupplier();
        $product = $this->createProduct($category, $supplier, [
            'nama_produk' => 'Gula Pasir',
        ]);

        $this->actingAs($gudang)->get('/products/create')->assertOk();
        $this->actingAs($gudang)->get(route('products.edit', $product))->assertOk();

        $this->actingAs($gudang)
            ->put(route('products.update', $product), $this->validProductPayload($category, $supplier, [
                'nama_produk' => 'Gula Pasir Premium',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
            ]))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'nama_produk' => 'Gula Pasir Premium',
            'harga_beli' => 12000,
            'harga_jual' => 15000,
        ]);

        $this->actingAs($gudang)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_kasir_can_only_view_product_data_and_cannot_manage_products(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory();
        $supplier = $this->createSupplier();
        $product = $this->createProduct($category, $supplier);

        $this->actingAs($kasir)
            ->get('/products')
            ->assertOk()
            ->assertSee($product->nama_produk);

        $this->actingAs($kasir)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->nama_produk);

        $this->actingAs($kasir)->get('/products/create')->assertForbidden();
        $this->actingAs($kasir)->post('/products', [])->assertForbidden();
        $this->actingAs($kasir)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($kasir)->put(route('products.update', $product), [])->assertForbidden();
        $this->actingAs($kasir)->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_gudang_sederhana_cannot_access_product_management(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory();
        $supplier = $this->createSupplier();
        $product = $this->createProduct($category, $supplier);

        $this->actingAs($gudang)->get('/products')->assertForbidden();
        $this->actingAs($gudang)->get('/products/create')->assertForbidden();
        $this->actingAs($gudang)->post('/products', $this->validProductPayload($category, $supplier))->assertForbidden();
        $this->actingAs($gudang)->get(route('products.edit', $product))->assertForbidden();
    }

    public function test_product_validation_requires_core_product_fields(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($owner)
            ->from('/products/create')
            ->post('/products', [])
            ->assertRedirect('/products/create')
            ->assertSessionHasErrors([
                'nama_produk',
                'category_id',
                'supplier_id',
                'harga_beli',
                'harga_jual',
                'stok_minimum',
                'satuan',
            ]);
    }

    private function createCategory(array $attributes = []): Category
    {
        return Category::create($attributes + [
            'nama_kategori' => 'Sembako',
            'slug' => 'sembako',
            'is_active' => true,
        ]);
    }

    private function createSupplier(array $attributes = []): Supplier
    {
        return Supplier::create($attributes + [
            'nama_supplier' => 'Supplier Utama',
            'is_active' => true,
        ]);
    }

    private function createProduct(Category $category, Supplier $supplier, array $attributes = []): Product
    {
        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'kode_produk' => 'PRD-TEST-'.fake()->unique()->numberBetween(1000, 9999),
            'nama_produk' => 'Minyak Goreng',
            'slug' => 'minyak-goreng-'.fake()->unique()->numberBetween(1000, 9999),
            'satuan' => 'pcs',
            'harga_beli' => 10000,
            'harga_jual' => 12500,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validProductPayload(Category $category, Supplier $supplier, array $overrides = []): array
    {
        return $overrides + [
            'nama_produk' => 'Minyak Goreng',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'harga_beli' => 10000,
            'harga_jual' => 12500,
            'stok_minimum' => 5,
            'satuan' => 'pcs',
            'deskripsi' => 'Produk kebutuhan harian.',
            'is_active' => '1',
        ];
    }
}
