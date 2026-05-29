<?php

namespace App\Http\Controllers;

use App\Mail\OwnerPasswordResetCodeMail;
use App\Mail\OwnerRegistrationVerificationCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function showOwnerPasswordResetForm(Request $request): View
    {
        return $this->showResetPasswordForm($request);
    }

    public function showResetPasswordForm(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => old('email', $request->query('email')),
        ]);
    }

    public function showUserRegisterForm(): View
    {
        return view('auth.register-user', [
            'allowedRoles' => $this->allowedUserRolesForMode(Auth::user()?->mode_app),
        ]);
    }

    public function showModeSelectionForm(): View
    {
        if (filled(Auth::user()?->mode_app)) {
            abort(403, 'Mode toko sudah dipilih.');
        }

        return view('auth.select-mode');
    }

    public function login(Request $request): RedirectResponse
    {
        $dataLogin = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($dataLogin, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Username atau password tidak valid.',
                ]);
        }

        if (! Auth::user()?->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Akun user sedang nonaktif.',
                ]);
        }

        $request->session()->regenerate();

        return $this->redirectToRoleHome();
    }

    public function sendPasswordResetCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $owner = User::query()
            ->where('email', $data['email'])
            ->where('role', 'owner')
            ->where('is_active', true)
            ->first();

        if ($owner) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table(config('auth.passwords.users.table'))
                ->updateOrInsert(
                    ['email' => $owner->email],
                    ['token' => Hash::make($code), 'created_at' => now()]
                );

            Mail::to($owner->email)->send(new OwnerPasswordResetCodeMail(
                ownerName: $owner->name,
                storeName: $owner->store_name,
                code: $code,
                expiresInMinutes: (int) config('auth.passwords.users.expire', 60),
            ));
        }

        return redirect()
            ->route('password.owner-reset', ['email' => $data['email']])
            ->with('status', 'Jika email owner ditemukan, kode verifikasi sudah dikirim. Pada local dev, cek log Laravel bila mailer masih menggunakan log.');
    }

    public function sendOwnerRegistrationCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table(config('auth.passwords.users.table'))
            ->updateOrInsert(
                ['email' => $data['email']],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

        Mail::to($data['email'])->send(new OwnerRegistrationVerificationCodeMail(
            ownerName: $request->input('name', 'Owner Baru'),
            storeName: $request->input('store_name'),
            code: $code,
            expiresInMinutes: (int) config('auth.passwords.users.expire', 60),
        ));

        return back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->with('status', 'Kode verifikasi registrasi sudah dikirim ke email owner. Pada local dev, cek log Laravel bila mailer masih menggunakan log.');
    }

    public function registerOwner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'alamat_toko' => ['required', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'verification_code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $registerToken = DB::table(config('auth.passwords.users.table'))
            ->where('email', $data['email'])
            ->first();

        $expiresAt = $registerToken?->created_at
            ? Carbon::parse($registerToken->created_at)->addMinutes((int) config('auth.passwords.users.expire', 60))
            : null;

        $isCodeInvalid = ! $registerToken
            || ! Hash::check($data['verification_code'], $registerToken->token)
            || ! $expiresAt
            || $expiresAt->isPast();

        if ($isCodeInvalid) {
            throw ValidationException::withMessages([
                'verification_code' => 'Kode verifikasi registrasi tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'store_name' => $data['store_name'],
            'alamat_toko' => $data['alamat_toko'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'owner',
            'mode_app' => null,
            'is_active' => true,
        ]);

        DB::table(config('auth.passwords.users.table'))
            ->where('email', $data['email'])
            ->delete();

        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectToRoleHome();
    }

    public function registerUser(Request $request): RedirectResponse
    {
        $allowedRoles = $this->allowedUserRolesForMode($request->user()?->mode_app);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys($allowedRoles))],
        ]);

        $data['email'] = $data['username'].'@toko.local';
        $data['store_name'] = $request->user()?->store_name;
        $data['alamat_toko'] = $request->user()?->alamat_toko;
        $data['mode_app'] = $request->user()?->mode_app;
        $data['is_active'] = true;

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'Pengguna berhasil dibuat.');
    }

    public function resetPasswordWithCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'verification_code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $owner = User::query()
            ->where('email', $data['email'])
            ->where('role', 'owner')
            ->first();

        if (! $owner) {
            throw ValidationException::withMessages([
                'email' => 'Email owner tidak ditemukan.',
            ]);
        }

        $resetToken = DB::table(config('auth.passwords.users.table'))
            ->where('email', $owner->email)
            ->first();

        $expiresAt = $resetToken?->created_at
            ? Carbon::parse($resetToken->created_at)->addMinutes((int) config('auth.passwords.users.expire', 60))
            : null;

        $isCodeInvalid = ! $resetToken
            || ! Hash::check($data['verification_code'], $resetToken->token)
            || ! $expiresAt
            || $expiresAt->isPast();

        if ($isCodeInvalid) {
            throw ValidationException::withMessages([
                'verification_code' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $owner->forceFill([
            'password' => $data['password'],
            'remember_token' => null,
        ])->save();

        DB::table(config('auth.passwords.users.table'))
            ->where('email', $owner->email)
            ->delete();

        return redirect()
            ->route('login')
            ->with('status', 'Password owner berhasil diperbarui. Silakan login kembali.');
    }

    public function redirectAuthenticatedUser(): RedirectResponse
    {
        return $this->redirectToRoleHome();
    }

    public function storeModeSelection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode_app' => ['required', Rule::in(['sederhana', 'lengkap'])],
        ]);

        $pengguna = $request->user();

        if (! $pengguna || $pengguna->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat memilih mode toko.');
        }

        $pengguna->forceFill([
            'mode_app' => $data['mode_app'],
        ])->save();

        return redirect()
            ->route('dashboard.index')
            ->with('status', 'Mode toko berhasil disimpan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectToRoleHome(): RedirectResponse
    {
        return match (Auth::user()?->role) {
            'owner' => blank(Auth::user()?->mode_app)
                ? redirect()->route('mode-selection.show')
                : redirect()->route('dashboard.index'),
            'gudang' => redirect()->route('stocks.role-home'),
            'kasir' => redirect()->route('transactions.pos'),
            default => redirect()->route('login'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function allowedUserRolesForMode(?string $modeApp): array
    {
        return match ($modeApp) {
            'sederhana' => ['kasir' => 'Kasir'],
            'lengkap' => ['kasir' => 'Kasir', 'gudang' => 'Gudang'],
            default => [],
        };
    }
}
