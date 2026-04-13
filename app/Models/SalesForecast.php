<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'periode_awal',
        'periode_akhir',
        'panjang_jendela',
        'nilai_moving_average',
        'prediksi_stok',
        'stok_aktual',
        'selisih_prediksi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'periode_awal' => 'date',
            'periode_akhir' => 'date',
            'panjang_jendela' => 'integer',
            'nilai_moving_average' => 'decimal:2',
            'prediksi_stok' => 'integer',
            'stok_aktual' => 'integer',
            'selisih_prediksi' => 'integer',
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
