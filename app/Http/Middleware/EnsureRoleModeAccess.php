<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleModeAccess
{
    public function handle(Request $request, Closure $next, string $access): Response|RedirectResponse
    {
        $pengguna = $request->user();

        if (! $pengguna) {
            return redirect()->route('login');
        }

        if ($pengguna->role === 'owner' && blank($pengguna->mode_app)) {
            return redirect()->route('mode-selection.show');
        }

        if ($pengguna->role !== 'owner' && ! in_array($pengguna->mode_app, ['sederhana', 'lengkap'], true)) {
            abort(403, 'Mode toko pengguna tidak valid.');
        }

        $bolehAkses = match ($access) {
            'owner' => $pengguna->role === 'owner',
            'stock-read' => $this->canReadStock($pengguna->role, $pengguna->mode_app),
            'inventory' => $this->canAccessInventory($pengguna->role, $pengguna->mode_app),
            'warehouse' => $this->canAccessWarehouse($pengguna->role, $pengguna->mode_app),
            'sales' => $this->canAccessSales($pengguna->role, $pengguna->mode_app),
            default => false,
        };

        if (! $bolehAkses) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }

    private function canReadStock(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner', 'kasir' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canAccessInventory(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canAccessWarehouse(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner', 'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canAccessSales(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner', 'kasir' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            default => false,
        };
    }
}
