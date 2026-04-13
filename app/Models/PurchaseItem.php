<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'nama_produk',
        'qty',
        'harga_beli',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_beli' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
