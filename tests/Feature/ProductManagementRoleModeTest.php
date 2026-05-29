<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SalesForecast;
use App\Models\StockMovement;
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
        $category = $this->createCategory(['store_name' => $owner->store_name]);

        $this->actingAs($owner)->get('/products')->assertOk();
        $this->actingAs($owner)->get('/products/create')->assertOk();

        $this->actingAs($owner)
            ->post('/products', $this->validProductPayload($category, null, [
                'nama_produk' => 'Beras Ramos 5kg',
            ]))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Beras Ramos 5kg',
            'category_id' => $category->id,
            'supplier_id' => null,
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
        $category = $this->createCategory(['store_name' => $gudang->store_name]);
        $supplier = $this->createSupplier(['store_name' => $gudang->store_name]);
        $product = $this->createProduct($category, $supplier, [
            'store_name' => $gudang->store_name,
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

    public function test_owner_sederhana_can_update_product_stock_from_edit_form_and_records_stock_movement(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory(['store_name' => $owner->store_name]);
        $product = $this->createProduct($category, null, [
            'store_name' => $owner->store_name,
            'stok' => 4,
        ]);

        $this->actingAs($owner)
            ->put(route('products.update', $product), $this->validProductPayload($category, null, [
                'stok' => 11,
            ]))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 11,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'jenis_pergerakan' => 'masuk',
            'qty' => 7,
            'referensi_tipe' => 'manual',
        ]);
    }

    public function test_owner_sederhana_and_gudang_lengkap_can_download_csv_template(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $responseSederhana = $this->actingAs($owner)
            ->get(route('products.template.download'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString(
            'nama_produk,kategori,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            $responseSederhana->streamedContent(),
        );

        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $responseLengkap = $this->actingAs($gudang)
            ->get(route('products.template.download'))
            ->assertOk();

        $this->assertStringContainsString(
            'nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            $responseLengkap->streamedContent(),
        );
    }

    public function test_owner_sederhana_can_destroy_all_products_when_unreferenced(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory(['store_name' => $owner->store_name]);
        $product = $this->createProduct($category, null, ['store_name' => $owner->store_name]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'referensi_tipe' => 'manual',
            'referensi_id' => null,
            'jenis_pergerakan' => 'masuk',
            'qty' => 3,
            'stok_sebelum' => 0,
            'stok_sesudah' => 3,
            'catatan' => 'Seed test.',
            'tanggal_pergerakan' => now(),
        ]);

        SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'periode_awal' => now()->subDays(30)->toDateString(),
            'periode_akhir' => now()->toDateString(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 12,
            'prediksi_stok' => 12,
            'stok_aktual' => 3,
            'selisih_prediksi' => 9,
            'catatan' => 'Seed test.',
        ]);

        $this->actingAs($owner)
            ->delete(route('products.destroy-all'))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('sales_forecasts', 0);
    }

    public function test_gudang_lengkap_can_destroy_all_products(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $category = $this->createCategory(['store_name' => $gudang->store_name]);
        $supplier = $this->createSupplier(['store_name' => $gudang->store_name]);
        $this->createProduct($category, $supplier, ['store_name' => $gudang->store_name]);

        $this->actingAs($gudang)
            ->delete(route('products.destroy-all'))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseCount('products', 0);
    }

    public function test_owner_lengkap_can_only_view_product_data(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $category = $this->createCategory(['nama_kategori' => 'Minuman', 'slug' => 'minuman', 'store_name' => $owner->store_name]);
        $supplier = $this->createSupplier(['nama_supplier' => 'Supplier Monitoring', 'store_name' => $owner->store_name]);
        $product = $this->createProduct($category, $supplier, [
            'store_name' => $owner->store_name,
            'nama_produk' => 'Teh Botol',
        ]);

        $this->actingAs($owner)
            ->get('/products')
            ->assertOk()
            ->assertSee($product->nama_produk)
            ->assertDontSee('Tambah Produk')
            ->assertDontSee('Edit');

        $this->actingAs($owner)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->nama_produk)
            ->assertDontSee('Edit Produk');

        $this->actingAs($owner)->get('/products/create')->assertForbidden();
        $this->actingAs($owner)->post('/products', [])->assertForbidden();
        $this->actingAs($owner)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($owner)->put(route('products.update', $product), [])->assertForbidden();
        $this->actingAs($owner)->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_kasir_can_only_view_product_data_and_cannot_manage_products(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $category = $this->createCategory(['store_name' => $kasir->store_name]);
        $supplier = $this->createSupplier(['store_name' => $kasir->store_name]);
        $product = $this->createProduct($category, $supplier, ['store_name' => $kasir->store_name]);

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
        $category = $this->createCategory(['store_name' => $gudang->store_name]);
        $supplier = $this->createSupplier(['store_name' => $gudang->store_name]);
        $product = $this->createProduct($category, $supplier, ['store_name' => $gudang->store_name]);

        $this->actingAs($gudang)->get('/products')->assertForbidden();
        $this->actingAs($gudang)->get('/products/create')->assertForbidden();
        $this->actingAs($gudang)->post('/products', $this->validProductPayload($category, $supplier))->assertForbidden();
        $this->actingAs($gudang)->get(route('products.edit', $product))->assertForbidden();
    }

    public function test_product_validation_requires_core_product_fields(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->from('/products/create')
            ->post('/products', [])
            ->assertRedirect('/products/create')
            ->assertSessionHasErrors([
                'nama_produk',
                'category_id',
                'harga_beli',
                'harga_jual',
                'stok_minimum',
                'satuan',
            ]);
    }

    public function test_products_are_scoped_per_store_between_sederhana_and_lengkap(): void
    {
        $ownerSederhana = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
            'store_name' => 'Toko Sederhana',
        ]);
        $gudangLengkap = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'store_name' => 'Toko Lengkap',
        ]);

        $simpleCategory = $this->createCategory([
            'nama_kategori' => 'Sembako',
            'slug' => 'sembako-sederhana',
            'store_name' => $ownerSederhana->store_name,
        ]);
        $completeCategory = $this->createCategory([
            'nama_kategori' => 'Elektronik',
            'slug' => 'elektronik-lengkap',
            'store_name' => $gudangLengkap->store_name,
        ]);
        $completeSupplier = $this->createSupplier([
            'nama_supplier' => 'Supplier Lengkap',
            'store_name' => $gudangLengkap->store_name,
        ]);

        $simpleProduct = $this->createProduct($simpleCategory, null, [
            'store_name' => $ownerSederhana->store_name,
            'nama_produk' => 'Produk Sederhana',
            'slug' => 'produk-sederhana',
        ]);
        $completeProduct = $this->createProduct($completeCategory, $completeSupplier, [
            'store_name' => $gudangLengkap->store_name,
            'nama_produk' => 'Produk Lengkap',
            'slug' => 'produk-lengkap',
        ]);

        $this->actingAs($ownerSederhana)
            ->get('/products')
            ->assertOk()
            ->assertSee($simpleProduct->nama_produk)
            ->assertDontSee($completeProduct->nama_produk);

        $this->actingAs($gudangLengkap)
            ->get('/products')
            ->assertOk()
            ->assertSee($completeProduct->nama_produk)
            ->assertDontSee($simpleProduct->nama_produk);
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

    private function createProduct(Category $category, ?Supplier $supplier = null, array $attributes = []): Product
    {
        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier?->id,
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
    private function validProductPayload(Category $category, ?Supplier $supplier = null, array $overrides = []): array
    {
        $payload = $overrides + [
            'nama_produk' => 'Minyak Goreng',
            'category_id' => $category->id,
            'harga_beli' => 10000,
            'harga_jual' => 12500,
            'stok_minimum' => 5,
            'satuan' => 'pcs',
            'deskripsi' => 'Produk kebutuhan harian.',
            'is_active' => '1',
        ];

        if ($supplier) {
            $payload['supplier_id'] = $supplier->id;
        }

        return $payload;
    }
}
