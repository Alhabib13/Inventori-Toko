<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    public function test_owner_is_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('owner'))->get('/');

        $response->assertRedirect(route('dashboard.index'));
    }

    public function test_gudang_is_redirected_to_stok(): void
    {
        $response = $this->actingAs($this->userWithRole('gudang'))->get('/');

        $response->assertRedirect(route('stocks.role-home'));
    }

    public function test_kasir_is_redirected_to_pos(): void
    {
        $response = $this->actingAs($this->userWithRole('kasir'))->get('/');

        $response->assertRedirect(route('transactions.pos'));
    }

    public function test_register_page_is_only_available_for_owner(): void
    {
        $this->get('/register')->assertRedirect('/login');
        $this->actingAs($this->userWithRole('kasir'))->get('/register')->assertForbidden();
        $this->actingAs($this->userWithRole('owner'))->get('/register')->assertOk();
    }

    private function userWithRole(string $role): User
    {
        return new User([
            'name' => ucfirst($role),
            'username' => $role,
            'email' => $role.'@toko.com',
            'role' => $role,
        ]);
    }
}
