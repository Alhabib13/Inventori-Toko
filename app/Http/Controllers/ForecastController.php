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
        $forecastSearch = trim((string) $request->string('forecast_search'));

        $forecastRows = SalesForecast::query()
            ->with('produk')
            ->whereHas('produk', function ($query) use ($user): void {
                $this->scopeToUserStore($query, $user);
            })
            ->when($forecastSearch !== '', function ($query) use ($forecastSearch): void {
                $query->where(function ($forecastQuery) use ($forecastSearch): void {
                    $forecastQuery
                        ->where('panjang_jendela', 'like', "%{$forecastSearch}%")
                        ->orWhereHas('produk', function ($productQuery) use ($forecastSearch): void {
                            $productQuery
                                ->where('nama_produk', 'like', "%{$forecastSearch}%")
                                ->orWhere('kode_produk', 'like', "%{$forecastSearch}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $restockProducts = Product::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
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
                ->whereHas('kasir', fn ($query) => $this->scopeToUserStore($query, $user))
                ->count(),
            'canManageForecasts' => $canManageForecasts,
            'forecastSearch' => $forecastSearch,
        ]);
    }

    public function create(): View
    {
        return view('forecasts.create', [
            'forecast' => null,
            'forecastProducts' => Product::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, request()->user()))
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
            ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
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
        $this->abortIfForecastOutsideStore($forecast, request()->user());
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
        $this->abortIfForecastOutsideStore($forecast, request()->user());

        return view('forecasts.edit', [
            'forecast' => $forecast->load('produk'),
            'forecastProducts' => Product::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, request()->user()))
                ->where('is_active', true)
                ->orderBy('nama_produk')
                ->get(),
            'canManageForecasts' => true,
        ]);
    }

    public function update(Request $request, SalesForecast $forecast): RedirectResponse
    {
        $this->abortIfForecastOutsideStore($forecast, $request->user());

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'periode_akhir' => ['required', 'date'],
            'panjang_jendela' => ['required', 'integer', 'min:1', 'max:12'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
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
        $this->abortIfForecastOutsideStore($forecast, request()->user());

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

    private function abortIfForecastOutsideStore(SalesForecast $forecast, $user): void
    {
        if (! $this->modelBelongsToUserStore($forecast->produk, $user)) {
            abort(403, 'Anda tidak memiliki akses ke prediksi stok ini.');
        }
    }
}
