<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    public function recordIncoming(
        Product $product,
        int $qty,
        ?User $user,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        return $this->recordMovement($product, $qty, 'masuk', $user, $note, $referenceType, $referenceId);
    }

    public function recordOutgoing(
        Product $product,
        int $qty,
        ?User $user,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        return $this->recordMovement($product, $qty, 'keluar', $user, $note, $referenceType, $referenceId);
    }

    private function recordMovement(
        Product $product,
        int $qty,
        string $type,
        ?User $user,
        ?string $note,
        ?string $referenceType,
        ?int $referenceId,
    ): StockMovement {
        if ($qty < 1) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah stok minimal 1.',
            ]);
        }

        return DB::transaction(function () use ($product, $qty, $type, $user, $note, $referenceType, $referenceId): StockMovement {
            $lockedProduct = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lockedProduct->stok;
            $stockAfter = $type === 'masuk'
                ? $stockBefore + $qty
                : $stockBefore - $qty;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Stok '.$lockedProduct->nama_produk.' tidak mencukupi.',
                ]);
            }

            $lockedProduct->update(['stok' => $stockAfter]);

            return StockMovement::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $user?->id,
                'referensi_tipe' => $referenceType,
                'referensi_id' => $referenceId,
                'jenis_pergerakan' => $type,
                'qty' => $qty,
                'stok_sebelum' => $stockBefore,
                'stok_sesudah' => $stockAfter,
                'catatan' => $note,
                'tanggal_pergerakan' => now(),
            ]);
        });
    }
}
