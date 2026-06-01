<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'nama_produk',
        'qty',
        'harga',
        'harga_beli',
        'subtotal',
    ];

    protected static function booted(): void
    {
        static::creating(function (TransactionItem $item): void {
            if (filled($item->harga_beli) || blank($item->product_id)) {
                return;
            }

            $item->harga_beli = (float) (Product::query()->whereKey($item->product_id)->value('harga_beli') ?? 0);
        });
    }

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga' => 'decimal:2',
            'harga_beli' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
