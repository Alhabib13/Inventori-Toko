<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'user_id',
        'tanggal_pembelian',
        'subtotal',
        'diskon',
        'ongkir',
        'total_bayar',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pembelian' => 'datetime',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'ongkir' => 'decimal:2',
            'total_bayar' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailItem(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
