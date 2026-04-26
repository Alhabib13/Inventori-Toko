<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    public function test_owner_is_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('owner', 'lengkap'))->get('/');

        $response->assertRedirect(route('dashboard.index'));
    }

    public function test_owner_without_mode_is_redirected_to_mode_selection(): void
    {
        $response = $this->actingAs($this->userWithRole('owner'))->get('/');

        $response->assertRedirect(route('mode-selection.show'));
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

    public function test_owner_self_registration_page_is_available_for_guest(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_user_register_page_is_only_available_for_owner(): void
    {
        $this->get('/register-user')->assertRedirect('/login');
        $this->actingAs($this->userWithRole('kasir'))->get('/register-user')->assertForbidden();
        $this->actingAs($this->userWithRole('owner', 'lengkap'))->get('/register-user')->assertOk();
    }

    public function test_owner_without_mode_cannot_access_dashboard(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->get('/dashboard')
            ->assertRedirect(route('mode-selection.show'));
    }

    private function userWithRole(string $role, ?string $modeApp = null): User
    {
        return new User([
            'name' => ucfirst($role),
            'username' => $role,
            'email' => $role.'@toko.com',
            'role' => $role,
            'mode_app' => $modeApp,
        ]);
    }
}
