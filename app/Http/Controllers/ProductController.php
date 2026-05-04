<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['kategori', 'supplier'])
            ->latest()
            ->paginate(10);

        return view('products.index', [
            'products' => $products,
            'canManageProducts' => $this->canManageProducts(request()->user()?->role, request()->user()?->mode_app),
        ]);
    }

    public function create(): View
    {
        return view('products.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        Product::create($data + [
            'kode_produk' => $this->makeProductCode(),
            'slug' => $this->makeUniqueSlug($data['nama_produk']),
            'stok' => 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['kategori', 'supplier']);

        return view('products.show', [
            'product' => $product,
            'canManageProducts' => $this->canManageProducts(request()->user()?->role, request()->user()?->mode_app),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', $this->formOptions() + compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request);

        $product->update($data + [
            'slug' => $this->makeUniqueSlug($data['nama_produk'], $product),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('nama_kategori')
                ->get(),
            'suppliers' => Supplier::query()
                ->where('is_active', true)
                ->orderBy('nama_supplier')
                ->get(),
        ];
    }

    private function canManageProducts(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => in_array($modeApp, ['sederhana', 'lengkap'], true),
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function makeProductCode(): string
    {
        do {
            $code = 'PRD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Product::query()->where('kode_produk', $code)->exists());

        return $code;
    }

    private function makeUniqueSlug(string $name, ?Product $product = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($product, fn ($query) => $query->whereKeyNot($product->getKey()))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
