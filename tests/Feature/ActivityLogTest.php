<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_user_creation_and_product_import_are_audited(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
            'username' => 'ownerlog',
            'password' => 'password123',
        ]);

        $this->post(route('login.process'), [
            'username' => 'ownerlog',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'store_id' => $owner->store_id,
            'action' => 'auth.login',
        ]);

        $this->actingAs($owner)
            ->post(route('users.register.process'), [
                'name' => 'Kasir Log',
                'username' => 'kasirlog',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'kasir',
            ])
            ->assertRedirect(route('users.index'));

        $createdUser = User::query()->where('username', 'kasirlog')->firstOrFail();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'action' => 'user.create',
            'target_type' => User::class,
            'target_id' => $createdUser->id,
        ]);

        $file = UploadedFile::fake()->createWithContent('produk-log.csv', implode("\n", [
            'nama_produk,kategori,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            'Produk Log,Sembako,pcs,10000,12000,5,1',
        ]));

        $this->actingAs($owner)
            ->post(route('products.import'), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'action' => 'product.import',
        ]);
        $this->assertSame(1, ActivityLog::query()->where('action', 'product.import')->firstOrFail()->metadata['imported']);
    }

    public function test_cancellations_and_bulk_deletes_are_audited(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $product = $this->createProduct($owner, ['stok' => 10]);

        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-LOG-001',
            'user_id' => $owner->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 12000,
            'total_bayar' => 12000,
            'nominal_bayar' => 12000,
            'kembalian' => 0,
            'status' => 'selesai',
        ]);
        $transaction->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga' => 12000,
            'harga_beli' => 10000,
            'subtotal' => 12000,
        ]);

        $this->actingAs($owner)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.show', $transaction));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'action' => 'transaction.cancel',
            'target_type' => Transaction::class,
            'target_id' => $transaction->id,
        ]);

        $bulkOwner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $bulkProduct = $this->createProduct($bulkOwner, ['nama_produk' => 'Produk Bulk Delete']);
        $this->actingAs($bulkOwner)
            ->delete(route('products.destroy-all'))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $bulkOwner->id,
            'action' => 'product.destroy_all',
        ]);
        $this->assertContains($bulkProduct->id, ActivityLog::query()->where('action', 'product.destroy_all')->firstOrFail()->metadata['product_ids']);

        $categoryOwner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);
        $category = Category::create([
            'nama_kategori' => 'Kategori Bulk Log',
            'slug' => 'kategori-bulk-log',
            'store_id' => $categoryOwner->store_id,
            'store_name' => $categoryOwner->store_name,
            'is_active' => true,
        ]);

        $this->actingAs($categoryOwner)
            ->delete(route('categories.destroy-all'))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $categoryOwner->id,
            'action' => 'category.destroy_all',
        ]);
        $this->assertContains($category->id, ActivityLog::query()->where('action', 'category.destroy_all')->firstOrFail()->metadata['category_ids']);
    }

    public function test_purchase_cancellation_and_supplier_bulk_delete_are_audited(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $supplier = $this->createSupplier($gudang);
        $product = $this->createProduct($gudang, [
            'supplier_id' => $supplier->id,
            'stok' => 5,
        ]);

        $purchase = Purchase::create([
            'kode_pembelian' => 'PO-LOG-001',
            'supplier_id' => $supplier->id,
            'user_id' => $gudang->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 20000,
            'diskon' => 0,
            'ongkir' => 0,
            'total_bayar' => 20000,
            'status' => 'selesai',
        ]);
        $purchase->detailItem()->create([
            'product_id' => $product->id,
            'nama_produk' => $product->nama_produk,
            'qty' => 1,
            'harga_beli' => 20000,
            'harga_beli_sebelum' => 10000,
            'subtotal' => 20000,
        ]);

        $this->actingAs($gudang)
            ->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.show', $purchase));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $gudang->id,
            'action' => 'purchase.cancel',
            'target_type' => Purchase::class,
            'target_id' => $purchase->id,
        ]);

        $supplierOwner = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $unusedSupplier = $this->createSupplier($supplierOwner);

        $this->actingAs($supplierOwner)
            ->delete(route('suppliers.destroy-all'))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supplierOwner->id,
            'action' => 'supplier.destroy_all',
        ]);
        $this->assertContains($unusedSupplier->id, ActivityLog::query()->where('action', 'supplier.destroy_all')->firstOrFail()->metadata['supplier_ids']);
    }

    private function createProduct(User $user, array $attributes = []): Product
    {
        $category = Category::create([
            'nama_kategori' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'store_id' => $user->store_id,
            'store_name' => $user->store_name,
            'is_active' => true,
        ]);

        $supplier = $this->createSupplier($user);

        return Product::create($attributes + [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'store_id' => $user->store_id,
            'store_name' => $user->store_name,
            'kode_produk' => 'PRD-'.fake()->unique()->numerify('####'),
            'nama_produk' => 'Produk '.fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'satuan' => 'pcs',
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'stok' => 0,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
    }

    private function createSupplier(User $user): Supplier
    {
        return Supplier::create([
            'nama_supplier' => fake()->unique()->company(),
            'nama_kontak' => fake()->name(),
            'telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'store_id' => $user->store_id,
            'store_name' => $user->store_name,
            'is_active' => true,
        ]);
    }
}
