<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_and_crud_suppliers_in_any_mode(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)->get('/suppliers')->assertOk();
        $this->actingAs($owner)->get('/suppliers/create')->assertOk();

        $this->actingAs($owner)
            ->post('/suppliers', [
                'nama_supplier' => 'PT Sumber Rejeki',
                'nama_kontak' => 'Budi',
                'telepon' => '081234567890',
                'alamat' => 'Jl. Raya No. 1',
            ])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'nama_supplier' => 'PT Sumber Rejeki',
        ]);

        $supplier = Supplier::where('nama_supplier', 'PT Sumber Rejeki')->first();

        $this->actingAs($owner)->get(route('suppliers.edit', $supplier))->assertOk();

        $this->actingAs($owner)
            ->put(route('suppliers.update', $supplier), [
                'nama_supplier' => 'PT Sumber Rejeki Jaya',
                'nama_kontak' => 'Budi',
                'telepon' => '081234567890',
                'alamat' => 'Jl. Raya No. 1',
            ])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'nama_supplier' => 'PT Sumber Rejeki Jaya',
        ]);

        $this->actingAs($owner)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_gudang_lengkap_can_crud_suppliers(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => 'CV Makmur',
            'nama_kontak' => 'Andi',
            'telepon' => '08987654321',
            'alamat' => 'Jl. Merdeka 2',
        ]);

        $this->actingAs($gudang)->get('/suppliers')->assertOk();
        $this->actingAs($gudang)->get('/suppliers/create')->assertOk();
        $this->actingAs($gudang)->get(route('suppliers.edit', $supplier))->assertOk();

        $this->actingAs($gudang)
            ->put(route('suppliers.update', $supplier), [
                'nama_supplier' => 'CV Makmur Sejahtera',
                'nama_kontak' => 'Andi',
                'telepon' => '08987654321',
                'alamat' => 'Jl. Merdeka 2',
            ])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'nama_supplier' => 'CV Makmur Sejahtera',
        ]);

        $this->actingAs($gudang)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_gudang_sederhana_cannot_access_suppliers(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'sederhana',
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => 'UD Maju',
            'nama_kontak' => 'Cici',
            'telepon' => '08777777',
            'alamat' => 'Jl. Sudirman 3',
        ]);

        $this->actingAs($gudang)->get('/suppliers')->assertForbidden();
        $this->actingAs($gudang)->get('/suppliers/create')->assertForbidden();
        $this->actingAs($gudang)->post('/suppliers', [])->assertForbidden();
        $this->actingAs($gudang)->get(route('suppliers.edit', $supplier))->assertForbidden();
    }

    public function test_kasir_cannot_access_suppliers(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => 'Toko Laris',
            'nama_kontak' => 'Dodi',
            'telepon' => '08555555',
            'alamat' => 'Jl. Diponegoro 4',
        ]);

        $this->actingAs($kasir)->get('/suppliers')->assertForbidden();
        $this->actingAs($kasir)->get('/suppliers/create')->assertForbidden();
        $this->actingAs($kasir)->post('/suppliers', [])->assertForbidden();
        $this->actingAs($kasir)->get(route('suppliers.edit', $supplier))->assertForbidden();
        $this->actingAs($kasir)->put(route('suppliers.update', $supplier), [])->assertForbidden();
        $this->actingAs($kasir)->delete(route('suppliers.destroy', $supplier))->assertForbidden();
    }

    public function test_supplier_validation_requires_minimum_fields(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($owner)
            ->from('/suppliers/create')
            ->post('/suppliers', [])
            ->assertRedirect('/suppliers/create')
            ->assertSessionHasErrors(['nama_supplier', 'nama_kontak', 'telepon', 'alamat']);
    }
}
