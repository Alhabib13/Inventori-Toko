<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_filtered_user_list_for_store_mode(): void
    {
        $owner = User::factory()->create([
            'name' => 'Owner Sederhana',
            'store_name' => 'Toko Maju',
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Toko',
            'store_name' => 'Toko Maju',
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        User::factory()->create([
            'name' => 'Gudang Lama',
            'store_name' => 'Toko Maju',
            'role' => 'gudang',
            'mode_app' => 'sederhana',
        ]);

        User::factory()->create([
            'name' => 'Kasir Toko Lain',
            'store_name' => 'Toko Seberang',
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($owner->name)
            ->assertSee($kasir->name)
            ->assertDontSee('Gudang Lama')
            ->assertDontSee('Kasir Toko Lain');
    }

    public function test_owner_can_view_user_detail(): void
    {
        $owner = User::factory()->create([
            'store_name' => 'Toko Maju',
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $gudang = User::factory()->create([
            'name' => 'Gudang Detail',
            'store_name' => 'Toko Maju',
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $this->actingAs($owner)
            ->get(route('users.show', $gudang))
            ->assertOk()
            ->assertSee('Gudang Detail')
            ->assertSee('Lengkap');
    }

    public function test_owner_lengkap_can_update_internal_user_role_and_status(): void
    {
        $owner = User::factory()->create([
            'store_name' => 'Toko Lengkap',
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Lama',
            'username' => 'kasirlama',
            'store_name' => 'Toko Lengkap',
            'role' => 'kasir',
            'mode_app' => 'lengkap',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('users.update', $kasir), [
                'name' => 'Gudang Baru',
                'username' => 'gudangbaru',
                'role' => 'gudang',
                'is_active' => 0,
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertRedirect(route('users.show', $kasir));

        $this->assertDatabaseHas('users', [
            'id' => $kasir->id,
            'name' => 'Gudang Baru',
            'username' => 'gudangbaru',
            'role' => 'gudang',
            'is_active' => false,
        ]);
        $this->assertTrue(Hash::check('passwordbaru123', $kasir->fresh()->password));
    }

    public function test_owner_sederhana_cannot_update_user_role_to_gudang(): void
    {
        $owner = User::factory()->create([
            'store_name' => 'Toko Sederhana',
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Mode Sederhana',
            'username' => 'kasirsederhana',
            'store_name' => 'Toko Sederhana',
            'role' => 'kasir',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)
            ->put(route('users.update', $kasir), [
                'name' => 'Kasir Mode Sederhana',
                'username' => 'kasirsederhana',
                'role' => 'gudang',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $kasir->id,
            'role' => 'kasir',
        ]);
    }

    public function test_nonactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'kasirnonaktif',
            'password' => 'password123',
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'is_active' => false,
        ]);

        $this->from('/login')
            ->post('/login', [
                'username' => 'kasirnonaktif',
                'password' => 'password123',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_owner_can_toggle_internal_user_status_from_list(): void
    {
        $owner = User::factory()->create([
            'store_name' => 'Toko Toggle',
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        $gudang = User::factory()->create([
            'store_name' => 'Toko Toggle',
            'role' => 'gudang',
            'mode_app' => 'lengkap',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->patch(route('users.toggle-status', $gudang))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $gudang->id,
            'is_active' => false,
        ]);
    }

    public function test_owner_sederhana_can_reset_kasir_password(): void
    {
        $owner = User::factory()->create([
            'store_name' => 'Toko Password',
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $kasir = User::factory()->create([
            'store_name' => 'Toko Password',
            'username' => 'kasirpassword',
            'password' => 'password123',
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('users.update', $kasir), [
                'name' => $kasir->name,
                'username' => 'kasirpassword',
                'role' => 'kasir',
                'is_active' => 1,
                'password' => 'passwordreset456',
                'password_confirmation' => 'passwordreset456',
            ])
            ->assertRedirect(route('users.show', $kasir));

        $this->assertTrue(Hash::check('passwordreset456', $kasir->fresh()->password));

        auth()->logout();

        $this->post('/login', [
            'username' => 'kasirpassword',
            'password' => 'passwordreset456',
        ])->assertRedirect(route('transactions.pos'));
    }
}
