<?php

namespace Tests\Feature;

use App\Mail\OwnerPasswordResetCodeMail;
use App\Mail\OwnerRegistrationVerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_owner_self_registration_creates_owner_and_logs_in(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => 'ownerbaru@toko.com',
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->post('/register', [
            'store_name' => 'Toko Sentosa',
            'alamat_toko' => 'Jl. Melati No. 10, Surabaya',
            'name' => 'Owner Baru',
            'username' => 'ownerbaru',
            'email' => 'ownerbaru@toko.com',
            'verification_code' => '123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('mode-selection.show'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Owner Baru',
            'store_name' => 'Toko Sentosa',
            'alamat_toko' => 'Jl. Melati No. 10, Surabaya',
            'username' => 'ownerbaru',
            'email' => 'ownerbaru@toko.com',
            'role' => 'owner',
            'mode_app' => null,
        ]);
    }

    public function test_owner_self_registration_shows_translated_validation_message(): void
    {
        $this->from('/register')->post('/register', [
            'store_name' => 'Toko Sentosa',
            'alamat_toko' => 'Jl. Melati No. 10, Surabaya',
            'name' => 'Owner Baru',
            'username' => 'ownerbaru',
            'email' => 'ownerbaru@toko.com',
            'verification_code' => '123456',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertRedirect('/register');

        $this->get('/register')
            ->assertSee('password minimal 8 karakter.')
            ->assertDontSee('validation.min.string');
    }

    public function test_login_page_has_owner_registration_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('href="'.route('register').'"', false);
    }

    public function test_login_page_has_forgot_password_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('href="'.route('password.request').'"', false);
    }

    public function test_owner_can_request_registration_verification_code_by_email(): void
    {
        Mail::fake();

        $this->post(route('register.owner.send-code'), [
            'email' => 'ownerbaru@toko.com',
            'name' => 'Owner Baru',
            'store_name' => 'Toko Sentosa',
        ])->assertRedirect();

        $registerToken = DB::table('password_reset_tokens')->where('email', 'ownerbaru@toko.com')->first();

        $this->assertNotNull($registerToken);

        Mail::assertSent(OwnerRegistrationVerificationCodeMail::class, function (OwnerRegistrationVerificationCodeMail $mail) {
            return $mail->hasTo('ownerbaru@toko.com') && strlen($mail->code) === 6;
        });
    }

    public function test_owner_can_request_password_reset_code_by_email(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'owner@toko.com',
        ]);

        $this->post(route('password.owner.email'), [
            'email' => $owner->email,
        ])->assertRedirect(route('password.owner.reset', ['email' => $owner->email]));

        $resetToken = DB::table('password_reset_tokens')->where('email', $owner->email)->first();

        $this->assertNotNull($resetToken);

        Mail::assertSent(OwnerPasswordResetCodeMail::class, function (OwnerPasswordResetCodeMail $mail) use ($owner) {
            return $mail->hasTo($owner->email) && strlen($mail->code) === 6;
        });
    }

    public function test_non_owner_email_does_not_receive_password_reset_code(): void
    {
        Mail::fake();

        $kasir = User::factory()->create([
            'role' => 'kasir',
            'email' => 'kasir@toko.com',
        ]);

        $this->post(route('password.owner.email'), [
            'email' => $kasir->email,
        ])->assertRedirect(route('password.owner.reset', ['email' => $kasir->email]));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $kasir->email,
        ]);

        Mail::assertNothingSent();
    }

    public function test_email_code_endpoints_are_rate_limited(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'owner-rate-limit@toko.com',
        ]);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('password.owner.email'), [
                'email' => $owner->email,
            ])->assertRedirect();
        }

        $this->post(route('password.owner.email'), [
            'email' => $owner->email,
        ])->assertTooManyRequests();

        Cache::flush();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('register.owner.send-code'), [
                'email' => "owner-register-rate-{$attempt}@toko.com",
            ])->assertRedirect();
        }

        $this->post(route('register.owner.send-code'), [
            'email' => 'owner-register-rate-final@toko.com',
        ])->assertTooManyRequests();
    }

    public function test_owner_can_reset_password_with_valid_verification_code(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'owner@toko.com',
            'password' => 'passwordlama',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $owner->email,
            'token' => Hash::make('654321'),
            'created_at' => now(),
        ]);

        $this->post(route('password.owner.update'), [
            'email' => $owner->email,
            'verification_code' => '654321',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('passwordbaru', $owner->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $owner->email,
        ]);
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

    public function test_mode_selection_page_is_only_available_for_owner_without_mode(): void
    {
        $this->get('/pilih-mode-toko')->assertRedirect('/login');
        $this->actingAs($this->userWithRole('kasir'))->get('/pilih-mode-toko')->assertForbidden();
        $this->actingAs($this->userWithRole('owner'))->get('/pilih-mode-toko')->assertOk();
        $this->actingAs($this->userWithRole('owner', 'lengkap'))->get('/pilih-mode-toko')->assertForbidden();
    }

    public function test_owner_can_save_store_mode_selection(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => null,
        ]);

        $this->actingAs($owner)
            ->post('/pilih-mode-toko', ['mode_app' => 'sederhana'])
            ->assertRedirect(route('dashboard.index'));

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'mode_app' => 'sederhana',
        ]);
    }

    public function test_authenticated_user_can_logout_via_get_route(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'sederhana',
            'is_active' => true,
        ]);

        $this->actingAs($kasir)
            ->get('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
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
