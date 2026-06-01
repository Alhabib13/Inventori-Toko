<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'categories', 'suppliers', 'products'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'store_id')) {
                return;
            }
        }

        DB::table('users')
            ->where('role', 'owner')
            ->whereNull('store_id')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(100, function ($owners): void {
                foreach ($owners as $owner) {
                    DB::table('users')
                        ->where('id', $owner->id)
                        ->update(['store_id' => (string) Str::uuid()]);
                }
            });

        $ownerGroups = DB::table('users')
            ->where('role', 'owner')
            ->whereNotNull('store_id')
            ->orderBy('id')
            ->get(['id', 'store_id', 'store_name', 'mode_app'])
            ->groupBy('store_name');

        foreach ($ownerGroups as $storeName => $owners) {
            $uniqueOwners = $owners->unique('store_id')->values();

            if ($uniqueOwners->count() === 1) {
                $storeId = $uniqueOwners->first()->store_id;

                DB::table('users')
                    ->where('role', '!=', 'owner')
                    ->where('store_name', $storeName)
                    ->update(['store_id' => $storeId]);

                foreach (['categories', 'suppliers', 'products'] as $tableName) {
                    DB::table($tableName)
                        ->where('store_name', $storeName)
                        ->update(['store_id' => $storeId]);
                }

                continue;
            }

            foreach ($uniqueOwners->groupBy('mode_app') as $modeApp => $modeOwners) {
                if ($modeOwners->count() !== 1) {
                    continue;
                }

                DB::table('users')
                    ->where('role', '!=', 'owner')
                    ->where('store_name', $storeName)
                    ->where('mode_app', $modeApp)
                    ->update(['store_id' => $modeOwners->first()->store_id]);
            }

            foreach (['categories', 'suppliers', 'products'] as $tableName) {
                DB::table($tableName)
                    ->where('store_name', $storeName)
                    ->update(['store_id' => null]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
