<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesForecast;
use App\Services\SalesForecastService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function __construct(private readonly SalesForecastService $salesForecastService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $isSimpleMode = $user?->mode_app === 'sederhana';
        $canManageForecasts = $this->canManageForecasts($user?->role, $user?->mode_app);

        $forecastRows = SalesForecast::query()
            ->with('produk')
            ->whereHas('produk', function ($query) use ($user): void {
                $query->where('store_name', $user?->store_name);
            })
            ->latest()
            ->paginate(10);

        $restockProducts = Product::query()
            ->where('store_name', $user?->store_name)
            ->where('is_active', true)
            ->orderByRaw('(stok_minimum - stok) DESC')
            ->orderBy('nama_produk')
            ->take(5)
            ->get()
            ->map(function (Product $product) use ($user) {
                $preview = $this->salesForecastService->buildForecast(
                    $product,
                    Carbon::now(),
                    $user?->role === 'gudang' ? 3 : 3,
                );

                return [
                    'product' => $product,
                    'forecast_qty' => $preview['prediksi_stok'],
                    'restock_gap' => $preview['selisih_prediksi'],
                ];
            });

        return view('forecasts.index', [
            'isSimpleMode' => $isSimpleMode,
            'forecastRows' => $forecastRows,
            'restockProducts' => $restockProducts,
            'transactionCount' => \App\Models\Transaction::query()
                ->whereHas('kasir', fn ($query) => $query->where('store_name', $user?->store_name))
                ->count(),
            'canManageForecasts' => $canManageForecasts,
        ]);
    }

    public function create(): View
    {
        return view('forecasts.create', [
            'forecast' => null,
            'forecastProducts' => Product::query()
                ->where('store_name', request()->user()?->store_name)
                ->where('is_active', true)
                ->orderBy('nama_produk')
                ->get(),
            'canManageForecasts' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'periode_akhir' => ['required', 'date'],
            'panjang_jendela' => ['required', 'integer', 'min:1', 'max:12'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('store_name', $request->user()?->store_name)
            ->firstOrFail();
        $forecast = $this->salesForecastService->storeForecast(
            $product,
            (int) $request->user()->id,
            Carbon::parse($validated['periode_akhir']),
            (int) $validated['panjang_jendela'],
            $validated['catatan'] ?? null,
        );

        return redirect()
            ->route('forecasts.show', $forecast)
            ->with('status', 'Prediksi stok berhasil dihitung dan disimpan.');
    }

    public function show(SalesForecast $forecast): View
    {
        if ($forecast->produk?->store_name !== null && $forecast->produk?->store_name !== request()->user()?->store_name) {
            abort(403, 'Anda tidak memiliki akses ke prediksi stok ini.');
        }
        $forecast->load('produk');
        $series = $this->salesForecastService->buildForecast(
            $forecast->produk,
            $forecast->periode_akhir->copy(),
            (int) $forecast->panjang_jendela,
        )['series'];

        return view('forecasts.show', [
            'forecast' => $forecast,
            'series' => $series,
            'canManageForecasts' => $this->canManageForecasts(request()->user()?->role, request()->user()?->mode_app),
        ]);
    }

    public function edit(SalesForecast $forecast): View
    {
        return view('forecasts.edit', [
            'forecast' => $forecast->load('produk'),
            'forecastProducts' => Product::query()
                ->where('store_name', request()->user()?->store_name)
                ->where('is_active', true)
                ->orderBy('nama_produk')
                ->get(),
            'canManageForecasts' => true,
        ]);
    }

    public function update(Request $request, SalesForecast $forecast): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'periode_akhir' => ['required', 'date'],
            'panjang_jendela' => ['required', 'integer', 'min:1', 'max:12'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($forecast->produk?->store_name !== null && $forecast->produk?->store_name !== $request->user()?->store_name) {
            abort(403, 'Anda tidak memiliki akses ke prediksi stok ini.');
        }

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('store_name', $request->user()?->store_name)
            ->firstOrFail();
        $forecast = $this->salesForecastService->updateForecast(
            $forecast,
            $product,
            (int) $request->user()->id,
            Carbon::parse($validated['periode_akhir']),
            (int) $validated['panjang_jendela'],
            $validated['catatan'] ?? null,
        );

        return redirect()
            ->route('forecasts.show', $forecast)
            ->with('status', 'Prediksi stok berhasil diperbarui.');
    }

    public function destroy(SalesForecast $forecast): RedirectResponse
    {
        if ($forecast->produk?->store_name !== null && $forecast->produk?->store_name !== request()->user()?->store_name) {
            abort(403, 'Anda tidak memiliki akses ke prediksi stok ini.');
        }
        $forecast->delete();

        return redirect()
            ->route('forecasts.index')
            ->with('status', 'Prediksi stok berhasil dihapus.');
    }

    private function canManageForecasts(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }
}
