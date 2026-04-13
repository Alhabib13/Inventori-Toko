<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'kode_produk',
        'nama_produk',
        'slug',
        'deskripsi',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function itemTransaksi(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function itemPembelian(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function pergerakanStok(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function prediksiPenjualan(): HasMany
    {
        return $this->hasMany(SalesForecast::class);
    }
}
