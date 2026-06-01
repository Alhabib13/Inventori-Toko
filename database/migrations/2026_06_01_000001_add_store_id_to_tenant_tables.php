<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->addStoreIdColumnIfMissing('users');
        $this->addStoreIdColumnIfMissing('categories');
        $this->addStoreIdColumnIfMissing('suppliers');
        $this->addStoreIdColumnIfMissing('products');

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

        $owners = DB::table('users')
            ->where('role', 'owner')
            ->whereNotNull('store_id')
            ->orderBy('id')
            ->get(['id', 'store_name', 'store_id'])
            ->groupBy('store_name');

        foreach ($owners as $storeName => $storeOwners) {
            DB::table('users')
                ->whereNull('store_id')
                ->where('store_name', $storeName)
                ->update(['store_id' => $storeOwners->first()->store_id]);

            foreach (['categories', 'suppliers', 'products'] as $table) {
                DB::table($table)
                    ->whereNull('store_id')
                    ->where('store_name', $storeName)
                    ->update(['store_id' => $storeOwners->first()->store_id]);
            }
        }

        DB::table('users')
            ->whereNull('store_id')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['store_id' => (string) Str::uuid()]);
                }
            });

        foreach (['categories', 'suppliers', 'products'] as $tableName) {
            DB::table($tableName)
                ->whereNull('store_id')
                ->update(['store_id' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        foreach (['products', 'suppliers', 'categories', 'users'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'store_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('store_id');
            });
        }
    }

    private function addStoreIdColumnIfMissing(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'store_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->uuid('store_id')->nullable()->after('id')->index();
        });
    }
};
