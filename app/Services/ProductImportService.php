<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductImportService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
    ) {}

    /**
     * @return array{imported:int, skipped:int}
     */
    public function importFromCsv(UploadedFile $file, User $user): array
    {
        $rows = $this->parseCsvRows($file, $user->mode_app === 'sederhana');

        if ($rows === []) {
            throw ValidationException::withMessages([
                'import_file' => 'File CSV tidak memiliki data produk untuk diimport.',
            ]);
        }

        return DB::transaction(function () use ($rows, $user): array {
            $importedCount = 0;
            $skippedCount = 0;
            $seenProducts = [];
            $storeId = $user->store_id;
            $storeName = $user->store_name;
            $categoriesByNormalizedName = $this->scopeToUserStore(Category::query(), $user)
                ->get()
                ->mapWithKeys(fn (Category $category) => [$this->normalizeEntityName($category->nama_kategori) => $category]);
            $suppliersByNormalizedName = $this->scopeToUserStore(Supplier::query(), $user)
                ->get()
                ->mapWithKeys(fn (Supplier $supplier) => [$this->normalizeEntityName($supplier->nama_supplier) => $supplier]);
            $productsByNormalizedName = $this->scopeToUserStore(Product::query(), $user)
                ->get()
                ->mapWithKeys(fn (Product $product) => [$this->normalizeEntityName($product->nama_produk) => $product]);
            $existingCategorySlugs = array_fill_keys(Category::query()
                ->pluck('slug')
                ->all(), true);
            $existingProductSlugs = array_fill_keys(Product::query()->pluck('slug')->all(), true);
            $existingProductCodes = array_fill_keys(Product::query()->pluck('kode_produk')->all(), true);

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $validated = $this->validateRow($row, $line);
                $normalizedProductName = $this->normalizeEntityName($validated['nama_produk']);

                if (isset($seenProducts[$normalizedProductName])) {
                    $skippedCount++;

                    continue;
                }

                $category = $this->findOrCreateCategory(
                    $validated['kategori'],
                    $categoriesByNormalizedName,
                    $existingCategorySlugs,
                    $storeId,
                    $storeName,
                );
                $supplier = $validated['supplier'] !== null
                    ? $this->findOrCreateSupplier(
                        $validated['supplier'],
                        $suppliersByNormalizedName,
                        $storeId,
                        $storeName,
                    )
                    : null;

                $existingProduct = $productsByNormalizedName->get($normalizedProductName);

                if ($existingProduct) {
                    $seenProducts[$normalizedProductName] = true;
                    $skippedCount++;

                    continue;
                }

                $product = Product::create([
                    'category_id' => $category->id,
                    'supplier_id' => $supplier?->id,
                    'kode_produk' => $this->makeProductCode($existingProductCodes),
                    'nama_produk' => $validated['nama_produk'],
                    'store_id' => $storeId,
                    'store_name' => $storeName,
                    'slug' => $this->makeUniqueSlug($validated['nama_produk'], $existingProductSlugs),
                    'deskripsi' => $validated['deskripsi'],
                    'satuan' => $validated['satuan'],
                    'harga_beli' => $validated['harga_beli'],
                    'harga_jual' => $validated['harga_jual'],
                    'stok' => 0,
                    'stok_minimum' => $validated['stok_minimum'],
                    'is_active' => true,
                ]);

                if ($validated['stok_awal'] > 0) {
                    $this->stockMovementService->recordIncoming(
                        $product,
                        $validated['stok_awal'],
                        $user,
                        'Import stok awal produk dari CSV.',
                        'import_produk',
                        null,
                    );
                }

                $importedCount++;
                $seenProducts[$normalizedProductName] = true;
                $productsByNormalizedName->put($normalizedProductName, $product);
            }

            return [
                'imported' => $importedCount,
                'skipped' => $skippedCount,
            ];
        });
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsvRows(UploadedFile $file, bool $allowWithoutSupplier): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'import_file' => 'File CSV tidak dapat dibaca.',
            ]);
        }

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'import_file' => 'Header CSV tidak ditemukan.',
            ]);
        }

        $header = array_map(function ($value): string {
            $normalized = trim((string) $value);
            $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $normalized) ?? $normalized;

            return Str::lower($normalized);
        }, $header);

        $expectedHeaderWithSupplier = [
            'nama_produk',
            'kategori',
            'supplier',
            'satuan',
            'harga_beli',
            'harga_jual',
            'stok_awal',
            'stok_minimum',
        ];
        $expectedHeaderWithoutSupplier = [
            'nama_produk',
            'kategori',
            'satuan',
            'harga_beli',
            'harga_jual',
            'stok_awal',
            'stok_minimum',
        ];

        if ($header !== $expectedHeaderWithSupplier && ! ($allowWithoutSupplier && $header === $expectedHeaderWithoutSupplier)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'import_file' => $allowWithoutSupplier
                    ? 'Format header CSV tidak sesuai. Gunakan urutan: nama_produk,kategori,satuan,harga_beli,harga_jual,stok_awal,stok_minimum atau nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum'
                    : 'Format header CSV tidak sesuai. Gunakan urutan: nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum',
            ]);
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($row) !== count($header)) {
                fclose($handle);

                throw ValidationException::withMessages([
                    'import_file' => 'Salah satu baris CSV memiliki jumlah kolom yang tidak sesuai dengan header.',
                ]);
            }

            $rows[] = array_combine($header, array_map(fn ($value) => trim((string) $value), $row));
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string|int|float|null>
     */
    private function validateRow(array $row, int $line): array
    {
        $messages = [];

        foreach (['nama_produk', 'kategori', 'satuan'] as $field) {
            if ($row[$field] === '') {
                $messages[] = "{$field} wajib diisi";
            }
        }

        if (array_key_exists('supplier', $row) && $row['supplier'] === '') {
            $messages[] = 'supplier wajib diisi';
        }

        foreach (['harga_beli', 'harga_jual', 'stok_awal', 'stok_minimum'] as $field) {
            if ($row[$field] === '' || ! is_numeric($row[$field])) {
                $messages[] = "{$field} harus berupa angka";
            }
        }

        $hargaBeli = (float) $row['harga_beli'];
        $hargaJual = (float) $row['harga_jual'];
        $stokAwal = (int) $row['stok_awal'];
        $stokMinimum = (int) $row['stok_minimum'];

        if ($hargaBeli < 0) {
            $messages[] = 'harga_beli tidak boleh negatif';
        }

        if ($hargaJual < $hargaBeli) {
            $messages[] = 'harga_jual tidak boleh lebih kecil dari harga_beli';
        }

        if ($stokAwal < 0) {
            $messages[] = 'stok_awal tidak boleh negatif';
        }

        if ($stokMinimum < 0) {
            $messages[] = 'stok_minimum tidak boleh negatif';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages([
                'import_file' => 'Baris '.$line.': '.implode(', ', $messages).'.',
            ]);
        }

        return [
            'nama_produk' => $row['nama_produk'],
            'kategori' => $row['kategori'],
            'supplier' => $row['supplier'] ?? null,
            'satuan' => $row['satuan'],
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok_awal' => $stokAwal,
            'stok_minimum' => $stokMinimum,
            'deskripsi' => null,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Category>  $categoriesByNormalizedName
     * @param  array<string, bool>  $existingCategorySlugs
     */
    private function findOrCreateCategory(
        string $name,
        $categoriesByNormalizedName,
        array &$existingCategorySlugs,
        ?string $storeId,
        string $storeName,
    ): Category
    {
        $normalizedName = $this->normalizeEntityName($name);
        $existing = $categoriesByNormalizedName->get($normalizedName);

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }

            return $existing;
        }

        $category = Category::create([
            'nama_kategori' => $name,
            'store_id' => $storeId,
            'store_name' => $storeName,
            'slug' => $this->makeUniqueCategorySlug($name, $existingCategorySlugs),
            'is_active' => true,
        ]);

        $categoriesByNormalizedName->put($normalizedName, $category);

        return $category;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Supplier>  $suppliersByNormalizedName
     */
    private function findOrCreateSupplier(
        string $name,
        $suppliersByNormalizedName,
        ?string $storeId,
        string $storeName,
    ): Supplier
    {
        $normalizedName = $this->normalizeEntityName($name);
        $existing = $suppliersByNormalizedName->get($normalizedName);

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }

            return $existing;
        }

        $supplier = Supplier::create([
            'nama_supplier' => $name,
            'store_id' => $storeId,
            'store_name' => $storeName,
            'is_active' => true,
        ]);

        $suppliersByNormalizedName->put($normalizedName, $supplier);

        return $supplier;
    }

    /**
     * @param  array<string, bool>  $existingProductCodes
     */
    private function makeProductCode(array &$existingProductCodes): string
    {
        do {
            $code = 'PRD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (isset($existingProductCodes[$code]));

        $existingProductCodes[$code] = true;

        return $code;
    }

    /**
     * @param  array<string, bool>  $existingProductSlugs
     */
    private function makeUniqueSlug(string $name, array &$existingProductSlugs): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (isset($existingProductSlugs[$slug])) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $existingProductSlugs[$slug] = true;

        return $slug;
    }

    /**
     * @param  array<string, bool>  $existingCategorySlugs
     */
    private function makeUniqueCategorySlug(string $name, array &$existingCategorySlugs): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (isset($existingCategorySlugs[$slug])) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $existingCategorySlugs[$slug] = true;

        return $slug;
    }

    private function normalizeEntityName(string $name): string
    {
        $normalized = Str::lower($name);
        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function scopeToUserStore($query, User $user)
    {
        if (filled($user->store_id)) {
            return $query->where(function ($tenantQuery) use ($user): void {
                $tenantQuery
                    ->where('store_id', $user->store_id)
                    ->orWhere(function ($legacyQuery) use ($user): void {
                        $legacyQuery
                            ->whereNull('store_id')
                            ->where('store_name', $user->store_name);
                    });
            });
        }

        return $query->where('store_name', $user->store_name);
    }
}
