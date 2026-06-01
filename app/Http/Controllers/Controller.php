<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    protected function scopeToUserStore($query, ?User $user, string $storeIdColumn = 'store_id', string $storeNameColumn = 'store_name')
    {
        if (filled($user?->store_id)) {
            return $query->where(function ($tenantQuery) use ($user, $storeIdColumn, $storeNameColumn): void {
                $tenantQuery->where($storeIdColumn, $user->store_id);

                if (! $this->hasAmbiguousStoreName($user)) {
                    $tenantQuery->orWhere(function ($legacyQuery) use ($user, $storeIdColumn, $storeNameColumn): void {
                        $legacyQuery
                            ->whereNull($storeIdColumn)
                            ->where($storeNameColumn, $user?->store_name);
                    });
                }
            });
        }

        return $query->whereRaw('1 = 0');
    }

    protected function modelBelongsToUserStore($model, ?User $user): bool
    {
        if (filled($model?->store_id) && filled($user?->store_id)) {
            return $model->store_id === $user->store_id;
        }

        return filled($user?->store_id)
            && blank($model?->store_id)
            && ! $this->hasAmbiguousStoreName($user)
            && $model?->store_name === $user?->store_name;
    }

    private function hasAmbiguousStoreName(?User $user): bool
    {
        if (blank($user?->store_name)) {
            return true;
        }

        return User::query()
            ->where('role', 'owner')
            ->where('store_name', $user->store_name)
            ->whereNotNull('store_id')
            ->distinct('store_id')
            ->count('store_id') > 1;
    }
}
