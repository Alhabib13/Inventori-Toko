<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'tanggal_transaksi',
        'total_item',
        'subtotal',
        'diskon',
        'pajak',
        'total_bayar',
        'nominal_bayar',
        'kembalian',
        'metode_pembayaran',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'datetime',
            'total_item' => 'integer',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_bayar' => 'decimal:2',
            'nominal_bayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
        ];
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailItem(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
