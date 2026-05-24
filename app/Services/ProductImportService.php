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

    public function importFromCsv(UploadedFile $file, User $user): int
    {
        $rows = $this->parseCsvRows($file, $user->mode_app === 'sederhana');

        if ($rows === []) {
            throw ValidationException::withMessages([
                'import_file' => 'File CSV tidak memiliki data produk untuk diimport.',
            ]);
        }

        return DB::transaction(function () use ($rows, $user): int {
            $importedCount = 0;

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $validated = $this->validateRow($row, $line);

                $category = $this->findOrCreateCategory($validated['kategori']);
                $supplier = $validated['supplier'] !== null
                    ? $this->findOrCreateSupplier($validated['supplier'])
                    : null;

                $existingProduct = Product::query()
                    ->get()
                    ->first(fn (Product $product) => $this->normalizeEntityName($product->nama_produk) === $this->normalizeEntityName($validated['nama_produk']));

                if ($existingProduct) {
                    throw ValidationException::withMessages([
                        'import_file' => "Baris {$line}: produk {$validated['nama_produk']} sudah ada. Import hanya untuk produk baru.",
                    ]);
                }

                $product = Product::create([
                    'category_id' => $category->id,
                    'supplier_id' => $supplier?->id,
                    'kode_produk' => $this->makeProductCode(),
                    'nama_produk' => $validated['nama_produk'],
                    'slug' => $this->makeUniqueSlug($validated['nama_produk']),
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
            }

            return $importedCount;
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

    private function findOrCreateCategory(string $name): Category
    {
        $normalizedName = $this->normalizeEntityName($name);
        $existing = Category::query()
            ->get()
            ->first(fn (Category $category) => $this->normalizeEntityName($category->nama_kategori) === $normalizedName);

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }

            return $existing;
        }

        return Category::create([
            'nama_kategori' => $name,
            'slug' => $this->makeUniqueCategorySlug($name),
            'is_active' => true,
        ]);
    }

    private function findOrCreateSupplier(string $name): Supplier
    {
        $normalizedName = $this->normalizeEntityName($name);
        $existing = Supplier::query()
            ->get()
            ->first(fn (Supplier $supplier) => $this->normalizeEntityName($supplier->nama_supplier) === $normalizedName);

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }

            return $existing;
        }

        return Supplier::create([
            'nama_supplier' => $name,
            'is_active' => true,
        ]);
    }

    private function makeProductCode(): string
    {
        do {
            $code = 'PRD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Product::query()->where('kode_produk', $code)->exists());

        return $code;
    }

    private function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function makeUniqueCategorySlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeEntityName(string $name): string
    {
        $normalized = Str::lower($name);
        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }
}
