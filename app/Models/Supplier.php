<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_supplier',
        'nama_kontak',
        'email',
        'telepon',
        'alamat',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
