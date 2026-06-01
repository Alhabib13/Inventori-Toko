<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationByStoreIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_owners_with_same_store_name_only_see_their_own_inventory_and_users(): void
    {
        [$ownerA, $ownerB] = $this->ownersWithSameStoreName();

        $categoryA = Category::create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'nama_kategori' => 'Kategori Toko A',
            'slug' => 'kategori-toko-a',
            'is_active' => true,
        ]);
        $categoryB = Category::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'nama_kategori' => 'Kategori Toko B',
            'slug' => 'kategori-toko-b',
            'is_active' => true,
        ]);

        $productA = Product::create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'category_id' => $categoryA->id,
            'kode_produk' => 'PRD-TOKO-A',
            'nama_produk' => 'Produk Rahasia Toko A',
            'slug' => 'produk-rahasia-toko-a',
            'harga_beli' => 1000,
            'harga_jual' => 1500,
            'stok' => 5,
            'stok_minimum' => 1,
            'is_active' => true,
        ]);
        $productB = Product::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'category_id' => $categoryB->id,
            'kode_produk' => 'PRD-TOKO-B',
            'nama_produk' => 'Produk Rahasia Toko B',
            'slug' => 'produk-rahasia-toko-b',
            'harga_beli' => 2000,
            'harga_jual' => 2500,
            'stok' => 8,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        $cashierA = User::factory()->create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'name' => 'Kasir Toko A',
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);
        User::factory()->create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'name' => 'Kasir Toko B',
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($ownerA)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($productA->nama_produk)
            ->assertDontSee($productB->nama_produk);

        $this->actingAs($ownerA)
            ->get(route('products.show', $productB))
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->get(route('categories.show', $categoryB))
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($cashierA->name)
            ->assertDontSee('Kasir Toko B');
    }

    public function test_owner_cannot_see_sales_or_purchases_from_other_store_with_same_name(): void
    {
        [$ownerA, $ownerB] = $this->ownersWithSameStoreName();
        $cashierA = User::factory()->create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);
        $cashierB = User::factory()->create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);
        $gudangA = User::factory()->create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $gudangB = User::factory()->create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $transactionA = Transaction::create([
            'kode_transaksi' => 'TRX-TENANT-A',
            'user_id' => $cashierA->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);
        $transactionB = Transaction::create([
            'kode_transaksi' => 'TRX-TENANT-B',
            'user_id' => $cashierB->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'nominal_bayar' => 20000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        $supplierA = Supplier::create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'nama_supplier' => 'Supplier Tenant A',
            'is_active' => true,
        ]);
        $supplierB = Supplier::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'nama_supplier' => 'Supplier Tenant B',
            'is_active' => true,
        ]);
        $purchaseA = Purchase::create([
            'kode_pembelian' => 'PO-TENANT-A',
            'supplier_id' => $supplierA->id,
            'user_id' => $gudangA->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 10000,
            'total_bayar' => 10000,
            'status' => 'selesai',
        ]);
        $purchaseB = Purchase::create([
            'kode_pembelian' => 'PO-TENANT-B',
            'supplier_id' => $supplierB->id,
            'user_id' => $gudangB->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'status' => 'selesai',
        ]);

        $this->actingAs($ownerA)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee($transactionA->kode_transaksi)
            ->assertDontSee($transactionB->kode_transaksi);

        $this->actingAs($ownerA)
            ->get(route('transactions.show', $transactionB))
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->get(route('purchases.index'))
            ->assertOk()
            ->assertSee($purchaseA->kode_pembelian)
            ->assertDontSee($purchaseB->kode_pembelian);

        $this->actingAs($ownerA)
            ->get(route('purchases.show', $purchaseB))
            ->assertForbidden();
    }

    public function test_transaction_edit_and_update_reject_other_store_records(): void
    {
        [$ownerA, $ownerB] = $this->ownersWithSameStoreName();
        $cashierB = User::factory()->create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);
        $transactionB = Transaction::create([
            'kode_transaksi' => 'TRX-EDIT-TENANT-B',
            'user_id' => $cashierB->id,
            'tanggal_transaksi' => now(),
            'total_item' => 1,
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'nominal_bayar' => 20000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
            'status' => 'selesai',
        ]);

        $this->actingAs($ownerA)
            ->get(route('transactions.edit', $transactionB))
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->put(route('transactions.update', $transactionB), [])
            ->assertForbidden();
    }

    public function test_purchase_edit_and_update_reject_other_store_records(): void
    {
        [$ownerA, $ownerB] = $this->ownersWithSameStoreName();
        $gudangA = User::factory()->create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $gudangB = User::factory()->create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $supplierB = Supplier::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'nama_supplier' => 'Supplier Purchase Tenant B',
            'is_active' => true,
        ]);
        $purchaseB = Purchase::create([
            'kode_pembelian' => 'PO-EDIT-TENANT-B',
            'supplier_id' => $supplierB->id,
            'user_id' => $gudangB->id,
            'tanggal_pembelian' => now(),
            'subtotal' => 20000,
            'total_bayar' => 20000,
            'status' => 'selesai',
        ]);

        $this->actingAs($gudangA)
            ->get(route('purchases.edit', $purchaseB))
            ->assertForbidden();

        $this->actingAs($gudangA)
            ->put(route('purchases.update', $purchaseB), [])
            ->assertForbidden();
    }

    public function test_forecast_edit_and_update_reject_other_store_records(): void
    {
        [$ownerA, $ownerB] = $this->ownersWithSameStoreName();
        $gudangA = User::factory()->create([
            'store_id' => $ownerA->store_id,
            'store_name' => $ownerA->store_name,
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
        $categoryB = Category::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'nama_kategori' => 'Kategori Forecast Tenant B',
            'slug' => 'kategori-forecast-tenant-b',
            'is_active' => true,
        ]);
        $productB = Product::create([
            'store_id' => $ownerB->store_id,
            'store_name' => $ownerB->store_name,
            'category_id' => $categoryB->id,
            'kode_produk' => 'PRD-FRC-TENANT-B',
            'nama_produk' => 'Produk Forecast Tenant B',
            'slug' => 'produk-forecast-tenant-b',
            'harga_beli' => 2000,
            'harga_jual' => 2500,
            'stok' => 8,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);
        $forecastB = SalesForecast::create([
            'product_id' => $productB->id,
            'user_id' => $ownerB->id,
            'periode_awal' => now()->subMonth()->startOfMonth(),
            'periode_akhir' => now()->endOfMonth(),
            'panjang_jendela' => 3,
            'nilai_moving_average' => 4,
            'prediksi_stok' => 4,
            'stok_aktual' => 8,
            'selisih_prediksi' => 0,
        ]);

        $this->actingAs($gudangA)
            ->get(route('forecasts.edit', $forecastB))
            ->assertForbidden();

        $this->actingAs($gudangA)
            ->put(route('forecasts.update', $forecastB), [])
            ->assertForbidden();
    }

    /**
     * @return array{User, User}
     */
    private function ownersWithSameStoreName(): array
    {
        return [
            User::factory()->create([
                'store_id' => '11111111-1111-4111-8111-111111111111',
                'store_name' => 'Toko Kembar',
                'role' => 'owner',
                'mode_app' => 'lengkap',
            ]),
            User::factory()->create([
                'store_id' => '22222222-2222-4222-8222-222222222222',
                'store_name' => 'Toko Kembar',
                'role' => 'owner',
                'mode_app' => 'lengkap',
            ]),
        ];
    }
}
