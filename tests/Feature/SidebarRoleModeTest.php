<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sederhana_sidebar_shows_operational_owner_menu(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk()
            ->assertSee('href="'.route('dashboard.index').'"', false)
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('href="'.route('stocks.role-home').'"', false)
            ->assertSee('href="'.route('reports.index').'"', false)
            ->assertSee('href="'.route('forecasts.index').'"', false)
            ->assertSee('href="'.route('users.index').'"', false)
            ->assertDontSee('href="'.route('suppliers.index').'"', false)
            ->assertDontSee('href="'.route('purchases.index').'"', false)
            ->assertDontSee('href="'.route('transactions.pos').'"', false);
    }

    public function test_owner_lengkap_sidebar_shows_monitoring_owner_menu(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk()
            ->assertSee('href="'.route('dashboard.index').'"', false)
            ->assertSee('href="'.route('reports.index').'"', false)
            ->assertSee('href="'.route('forecasts.index').'"', false)
            ->assertSee('href="'.route('users.index').'"', false)
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('href="'.route('stocks.role-home').'"', false)
            ->assertDontSee('href="'.route('suppliers.index').'"', false)
            ->assertDontSee('href="'.route('purchases.index').'"', false)
            ->assertDontSee('href="'.route('transactions.pos').'"', false);
    }

    public function test_gudang_lengkap_sidebar_shows_gudang_menu(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $response = $this->actingAs($gudang)->get('/stok');

        $response->assertOk()
            ->assertSee('href="'.route('stocks.role-home').'"', false)
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('href="'.route('suppliers.index').'"', false)
            ->assertSee('href="'.route('purchases.index').'"', false)
            ->assertSee('href="'.route('stocks.notifications').'"', false)
            ->assertSee('href="'.route('forecasts.index').'"', false)
            ->assertDontSee('href="'.route('dashboard.index').'"', false)
            ->assertDontSee('href="'.route('users.index').'"', false)
            ->assertDontSee('href="'.route('transactions.pos').'"', false);
    }

    public function test_kasir_sidebar_only_shows_kasir_menu(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $response = $this->actingAs($kasir)->get('/pos');

        $response->assertOk()
            ->assertSee('href="'.route('transactions.pos').'"', false)
            ->assertSee('href="'.route('transactions.index').'"', false)
            ->assertSee('href="'.route('stocks.role-home').'"', false)
            ->assertDontSee('href="'.route('dashboard.index').'"', false)
            ->assertDontSee('href="'.route('products.index').'"', false)
            ->assertDontSee('href="'.route('categories.index').'"', false)
            ->assertDontSee('href="'.route('suppliers.index').'"', false)
            ->assertDontSee('href="'.route('purchases.index').'"', false)
            ->assertDontSee('href="'.route('users.index').'"', false)
            ->assertDontSee('href="'.route('forecasts.index').'"', false);
    }
}
