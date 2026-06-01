<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesForecast;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesForecastService
{
    public function buildForecast(Product $product, Carbon $periodEnd, int $windowMonths): array
    {
        $windowMonths = max(1, min($windowMonths, 12));
        $periodEnd = $periodEnd->copy()->endOfMonth();
        $periodStart = $periodEnd->copy()->subMonths($windowMonths - 1)->startOfMonth();
        $months = collect(range(0, $windowMonths - 1))
            ->map(fn (int $offset) => $periodStart->copy()->addMonths($offset)->startOfMonth());

        $transactionItems = TransactionItem::query()
            ->select(['transaction_items.qty', 'transactions.tanggal_transaksi'])
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transaction_items.product_id', $product->id)
            ->where('transactions.status', 'selesai')
            ->whereBetween('transactions.tanggal_transaksi', [$periodStart, $periodEnd])
            ->get();

        $monthlySales = $transactionItems
            ->groupBy(fn (TransactionItem $item) => Carbon::parse($item->getAttribute('tanggal_transaksi'))->format('Y-m-01'))
            ->map(fn (Collection $items): int => (int) $items->sum('qty'));

        $series = $months->map(function (Carbon $month) use ($monthlySales): int {
            return (int) ($monthlySales[$month->format('Y-m-01')] ?? 0);
        });

        $movingAverage = round($series->sum() / $windowMonths, 2);
        $forecastQty = (int) ceil($movingAverage);
        $restockGap = max($forecastQty - (int) $product->stok, 0);

        return [
            'periode_awal' => $periodStart->toDateString(),
            'periode_akhir' => $periodEnd->toDateString(),
            'panjang_jendela' => $windowMonths,
            'nilai_moving_average' => $movingAverage,
            'prediksi_stok' => $forecastQty,
            'stok_aktual' => (int) $product->stok,
            'selisih_prediksi' => $restockGap,
            'catatan_default' => $restockGap > 0
                ? 'Rekomendasi restock minimal '.$restockGap.' '.$product->satuan.' untuk menjaga stok periode berikutnya.'
                : 'Stok saat ini masih cukup untuk menutup prediksi penjualan periode berikutnya.',
            'series' => $this->mapSeries($months, $series),
        ];
    }

    public function storeForecast(Product $product, int $userId, Carbon $periodEnd, int $windowMonths, ?string $note = null): SalesForecast
    {
        $forecast = $this->buildForecast($product, $periodEnd, $windowMonths);

        return SalesForecast::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'periode_awal' => $forecast['periode_awal'],
            'periode_akhir' => $forecast['periode_akhir'],
            'panjang_jendela' => $forecast['panjang_jendela'],
            'nilai_moving_average' => $forecast['nilai_moving_average'],
            'prediksi_stok' => $forecast['prediksi_stok'],
            'stok_aktual' => $forecast['stok_aktual'],
            'selisih_prediksi' => $forecast['selisih_prediksi'],
            'catatan' => filled($note) ? $note : $forecast['catatan_default'],
        ]);
    }

    public function updateForecast(SalesForecast $forecast, Product $product, int $userId, Carbon $periodEnd, int $windowMonths, ?string $note = null): SalesForecast
    {
        $payload = $this->buildForecast($product, $periodEnd, $windowMonths);

        $forecast->update([
            'product_id' => $product->id,
            'user_id' => $userId,
            'periode_awal' => $payload['periode_awal'],
            'periode_akhir' => $payload['periode_akhir'],
            'panjang_jendela' => $payload['panjang_jendela'],
            'nilai_moving_average' => $payload['nilai_moving_average'],
            'prediksi_stok' => $payload['prediksi_stok'],
            'stok_aktual' => $payload['stok_aktual'],
            'selisih_prediksi' => $payload['selisih_prediksi'],
            'catatan' => filled($note) ? $note : $payload['catatan_default'],
        ]);

        return $forecast->refresh();
    }

    private function mapSeries(Collection $months, Collection $series): Collection
    {
        return $months->values()->map(function (Carbon $month, int $index) use ($series): array {
            return [
                'label' => $month->translatedFormat('M Y'),
                'qty' => (int) $series[$index],
            ];
        });
    }
}
