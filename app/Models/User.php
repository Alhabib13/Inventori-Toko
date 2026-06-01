<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'store_id',
        'store_name',
        'alamat_toko',
        'username',
        'email',
        'password',
        'role',
        'mode_app',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (filled($user->store_id)) {
                return;
            }

            if ($user->role === 'owner') {
                $user->store_id = (string) Str::uuid();

                return;
            }

            $owner = self::query()
                ->where('role', 'owner')
                ->where('store_name', $user->store_name)
                ->when(filled($user->mode_app), fn ($query) => $query->where('mode_app', $user->mode_app))
                ->latest('id')
                ->first();

            $user->store_id = $owner?->store_id ?: (string) Str::uuid();
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
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
