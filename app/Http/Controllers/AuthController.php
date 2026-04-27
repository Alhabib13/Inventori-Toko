<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

        $request->session()->regenerate();

        return $this->redirectToRoleHome();
    }

    public function registerOwner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'store_name' => $data['store_name'],
            'username' => $data['username'],
            'email' => $data['username'].'@toko.local',
            'password' => $data['password'],
            'role' => 'owner',
            'mode_app' => null,
        ]);

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
        $data['mode_app'] = $request->user()?->mode_app;

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'Pengguna berhasil dibuat.');
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
