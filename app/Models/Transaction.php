<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
            'total_item' => 'integer',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_bayar' => 'decimal:2',
            'nominal_bayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
        ];
    }

    protected function tanggalTransaksi(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ? Carbon::parse($value, 'UTC')->setTimezone(config('app.timezone'))
                : null,
            set: fn ($value) => [
                'tanggal_transaksi' => $value
                    ? Carbon::parse($value, config('app.timezone'))
                        ->utc()
                        ->format('Y-m-d H:i:s')
                    : null,
            ],
        );
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
