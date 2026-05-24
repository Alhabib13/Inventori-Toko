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
            'reports' => $this->canAccessReports($pengguna->role, $pengguna->mode_app),
            'product-read' => $this->canReadProducts($pengguna->role, $pengguna->mode_app),
            'stock-read' => $this->canReadStock($pengguna->role, $pengguna->mode_app),
            'stock-manage' => $this->canManageStock($pengguna->role, $pengguna->mode_app),
            'low-stock' => $this->canAccessLowStockNotifications($pengguna->role, $pengguna->mode_app),
            'inventory-read' => $this->canReadInventory($pengguna->role, $pengguna->mode_app),
            'inventory-manage' => $this->canManageInventory($pengguna->role, $pengguna->mode_app),
            'supplier-manage' => $this->canManageSuppliers($pengguna->role, $pengguna->mode_app),
            'purchase-read' => $this->canReadPurchases($pengguna->role, $pengguna->mode_app),
            'purchase-manage' => $this->canManagePurchases($pengguna->role, $pengguna->mode_app),
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
            'owner' => $modeApp === 'sederhana',
            'kasir' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canReadProducts(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner', 'kasir' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canManageStock(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canAccessReports(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canReadInventory(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canManageInventory(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canAccessLowStockNotifications(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canManageSuppliers(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canManagePurchases(string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function canReadPurchases(string $role, ?string $modeApp): bool
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
