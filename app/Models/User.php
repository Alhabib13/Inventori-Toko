<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'mode_app',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(Purchase::class);
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
