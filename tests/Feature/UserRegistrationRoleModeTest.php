<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sederhana_owner_sees_only_kasir_role_option(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->get('/register-user')
            ->assertOk()
            ->assertSee('value="kasir"', false)
            ->assertDontSee('value="gudang"', false);
    }

    public function test_lengkap_owner_sees_kasir_and_gudang_role_options(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'store_name' => 'Toko Lengkap',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($owner)
            ->get('/register-user')
            ->assertOk()
            ->assertSee('value="kasir"', false)
            ->assertSee('value="gudang"', false);
    }

    public function test_sederhana_owner_cannot_create_gudang_user(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->post('/register-user', $this->validUserPayload(['role' => 'gudang']))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'username' => 'userbaru',
            'role' => 'gudang',
        ]);
    }

    public function test_lengkap_owner_can_create_gudang_user(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'store_name' => 'Toko Lengkap',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($owner)
            ->post('/register-user', $this->validUserPayload(['role' => 'gudang']))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'userbaru',
            'store_name' => 'Toko Lengkap',
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validUserPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'User Baru',
            'username' => 'userbaru',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kasir',
        ], $overrides);
    }
}
