<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImportFromCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sederhana_can_import_products_and_initial_stock_from_csv(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $file = UploadedFile::fake()->createWithContent('produk-awal.csv', implode("\n", [
            'nama_produk,kategori,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            'Beras Premium,Sembako,karung,80000,95000,12,4',
            'Minyak Goreng,Liquid,liter,14000,16500,20,6',
        ]));

        $this->actingAs($owner)
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('categories', [
            'nama_kategori' => 'Sembako',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Beras Premium',
            'satuan' => 'karung',
            'stok' => 12,
            'stok_minimum' => 4,
            'supplier_id' => null,
        ]);
        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Minyak Goreng',
            'satuan' => 'liter',
            'stok' => 20,
            'stok_minimum' => 6,
            'supplier_id' => null,
        ]);
        $this->assertDatabaseCount('suppliers', 0);

        $product = Product::query()->where('nama_produk', 'Beras Premium')->firstOrFail();

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'referensi_tipe' => 'import_produk',
            'jenis_pergerakan' => 'masuk',
            'qty' => 12,
            'stok_sebelum' => 0,
            'stok_sesudah' => 12,
        ]);
    }

    public function test_gudang_lengkap_can_import_products_with_existing_category_and_supplier(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        Category::create([
            'nama_kategori' => 'Sembako',
            'slug' => 'sembako',
            'is_active' => true,
        ]);
        $supplier = Supplier::create([
            'nama_supplier' => 'Supplier Lama',
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->createWithContent('produk-gudang.csv', implode("\n", [
            'nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            'Gula Pasir,Sembako,Supplier   Lama.,kg,12000,14500,15,5',
        ]));

        $this->actingAs($gudang)
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Gula Pasir',
            'supplier_id' => $supplier->id,
            'stok' => 15,
            'stok_minimum' => 5,
        ]);
        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_import_rejects_invalid_csv_header_and_rolls_back_all_changes(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $file = UploadedFile::fake()->createWithContent('produk-rusak.csv', implode("\n", [
            'nama_produk,kategori,harga_beli',
            'Beras,Sembako,10000',
        ]));

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('import_file');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('suppliers', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_read_only_or_unrelated_roles_cannot_import_products(): void
    {
        $ownerLengkap = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);
        $file = UploadedFile::fake()->createWithContent('produk.csv', implode("\n", [
            'nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            'Telur,Protein,Supplier Ayam,pcs,2000,2500,30,10',
        ]));

        $this->actingAs($ownerLengkap)
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertForbidden();

        $this->actingAs($kasir)
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
