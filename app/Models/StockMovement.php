<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'referensi_tipe',
        'referensi_id',
        'jenis_pergerakan',
        'qty',
        'stok_sebelum',
        'stok_sesudah',
        'catatan',
        'tanggal_pergerakan',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'stok_sebelum' => 'integer',
            'stok_sesudah' => 'integer',
            'tanggal_pergerakan' => 'datetime',
        ];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
