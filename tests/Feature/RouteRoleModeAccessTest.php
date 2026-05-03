<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteRoleModeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_only_access_pos_transaction_history_and_read_only_stock(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($kasir)->get('/pos')->assertOk();
        $this->actingAs($kasir)->get('/transactions')->assertOk();
        $this->actingAs($kasir)->get('/stok')->assertOk();

        $this->actingAs($kasir)->get('/products/create')->assertForbidden();
        $this->actingAs($kasir)->post('/products')->assertForbidden();
        $this->actingAs($kasir)->get('/stocks/create')->assertForbidden();
        $this->actingAs($kasir)->post('/stocks')->assertForbidden();
        $this->actingAs($kasir)->get('/suppliers')->assertForbidden();
        $this->actingAs($kasir)->get('/purchases')->assertForbidden();
        $this->actingAs($kasir)->get('/transactions/create')->assertForbidden();
    }

    public function test_gudang_lengkap_can_access_warehouse_features_but_not_owner_or_sales_features(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($gudang)->get('/products')->assertOk();
        $this->actingAs($gudang)->get('/stocks')->assertOk();
        $this->actingAs($gudang)->get('/suppliers')->assertOk();
        $this->actingAs($gudang)->get('/purchases')->assertOk();
        $this->actingAs($gudang)->get('/stok/notifikasi')->assertOk();

        $this->actingAs($gudang)->get('/pos')->assertForbidden();
        $this->actingAs($gudang)->get('/users')->assertForbidden();
        $this->actingAs($gudang)->get('/reports')->assertForbidden();
    }

    public function test_gudang_sederhana_is_not_a_valid_mode_for_gudang_features(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($gudang)->get('/stok')->assertForbidden();
        $this->actingAs($gudang)->get('/products')->assertForbidden();
        $this->actingAs($gudang)->get('/suppliers')->assertForbidden();
        $this->actingAs($gudang)->get('/purchases')->assertForbidden();
        $this->actingAs($gudang)->get('/stok/notifikasi')->assertForbidden();
    }

    public function test_owner_access_follows_selected_store_mode(): void
    {
        $ownerSederhana = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($ownerSederhana)->get('/dashboard')->assertOk();
        $this->actingAs($ownerSederhana)->get('/products')->assertOk();
        $this->actingAs($ownerSederhana)->get('/stok')->assertOk();
        $this->actingAs($ownerSederhana)->get('/suppliers')->assertForbidden();
        $this->actingAs($ownerSederhana)->get('/purchases')->assertForbidden();

        $ownerLengkap = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($ownerLengkap)->get('/suppliers')->assertOk();
        $this->actingAs($ownerLengkap)->get('/purchases')->assertOk();
    }

    public function test_owner_without_mode_is_redirected_to_mode_selection_for_protected_routes(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => null,
        ]);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertRedirect(route('mode-selection.show'));

        $this->actingAs($owner)
            ->get('/products')
            ->assertRedirect(route('mode-selection.show'));
    }
}
