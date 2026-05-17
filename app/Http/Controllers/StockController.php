<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        return $this->stockListingView();
    }

    public function notifications(): View
    {
        return $this->stockListingView(true);
    }

    public function create(): View
    {
        return view('stocks.create', [
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('nama_produk')
                ->get(),
        ]);
    }

    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $stockMovementService->recordIncoming(
            product: $product,
            qty: (int) $data['qty'],
            user: $request->user(),
            note: $data['catatan'] ?? null,
            referenceType: 'manual',
        );

        return redirect()
            ->route('stocks.index')
            ->with('status', 'Stok masuk berhasil dicatat.');
    }

    public function show(StockMovement $stock): View
    {
        $stock->load(['produk', 'pengguna']);

        return view('stocks.show', ['stock' => $stock]);
    }

    public function edit(StockMovement $stock): View
    {
        return view('stocks.edit', ['stock' => $stock]);
    }

    public function update(Request $request, StockMovement $stock): RedirectResponse
    {
        return redirect()->route('stocks.index');
    }

    public function destroy(StockMovement $stock): RedirectResponse
    {
        return redirect()->route('stocks.index');
    }

    private function stockListingView(bool $showLowStockOnly = false): View
    {
        $products = Product::query()
            ->with(['kategori', 'supplier'])
            ->when($showLowStockOnly, fn ($query) => $query->whereColumn('stok', '<=', 'stok_minimum'))
            ->orderBy('nama_produk')
            ->get();

        $movements = StockMovement::query()
            ->with(['produk', 'pengguna'])
            ->latest('tanggal_pergerakan')
            ->paginate(10);

        return view('stocks.index', [
            'products' => $products,
            'movements' => $movements,
            'canManageStock' => $this->canManageStock(request()->user()?->role, request()->user()?->mode_app),
            'showLowStockOnly' => $showLowStockOnly,
        ]);
    }

    private function canManageStock(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }
}
