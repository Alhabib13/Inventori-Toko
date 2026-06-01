<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    protected function scopeToUserStore($query, ?User $user, string $storeIdColumn = 'store_id', string $storeNameColumn = 'store_name')
    {
        if (filled($user?->store_id)) {
            return $query->where(function ($tenantQuery) use ($user, $storeIdColumn, $storeNameColumn): void {
                $tenantQuery
                    ->where($storeIdColumn, $user->store_id)
                    ->orWhere(function ($legacyQuery) use ($user, $storeIdColumn, $storeNameColumn): void {
                        $legacyQuery
                            ->whereNull($storeIdColumn)
                            ->where($storeNameColumn, $user?->store_name);
                    });
            });
        }

        return $query->where($storeNameColumn, $user?->store_name);
    }

    protected function modelBelongsToUserStore($model, ?User $user): bool
    {
        if (filled($model?->store_id) && filled($user?->store_id)) {
            return $model->store_id === $user->store_id;
        }

        return blank($model?->store_name) || $model->store_name === $user?->store_name;
    }
}
