<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $storeName = $request->user()?->store_name;
        $search = trim((string) $request->string('search'));

        $categories = Category::query()
            ->where('store_name', $storeName)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($categoryQuery) use ($search) {
                    $categoryQuery
                        ->where('nama_kategori', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            'canManageCategories' => $this->canManageCategories($request->user()?->role, $request->user()?->mode_app),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $storeName = $request->user()?->store_name;
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')->where(fn ($query) => $query->where('store_name', $storeName))],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug($validated['nama_kategori']);
        $validated['store_name'] = $storeName;

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category): View
    {
        $this->abortIfCategoryOutsideStore($category, request()->user()?->store_name);
        return view('categories.show', [
            'category' => $category,
            'canManageCategories' => $this->canManageCategories(request()->user()?->role, request()->user()?->mode_app),
        ]);
    }

    public function edit(Category $category): View
    {
        $this->abortIfCategoryOutsideStore($category, request()->user()?->store_name);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->abortIfCategoryOutsideStore($category, $request->user()?->store_name);
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')->where(fn ($query) => $query->where('store_name', $request->user()?->store_name))->ignore($category->id)],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug($validated['nama_kategori'], $category);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->abortIfCategoryOutsideStore($category, request()->user()?->store_name);

        if (Product::query()->where('category_id', $category->id)->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('success', 'Kategori tidak bisa dihapus karena masih dipakai produk.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        abort_unless($this->canManageCategories($request->user()?->role, $request->user()?->mode_app), 403);

        $categoryIds = Category::query()
            ->where('store_name', $request->user()?->store_name)
            ->pluck('id');

        if ($categoryIds->isEmpty()) {
            return redirect()
                ->route('categories.index')
                ->with('success', 'Tidak ada kategori yang perlu dihapus.');
        }

        if (Product::query()->whereIn('category_id', $categoryIds)->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('success', 'Hapus semua kategori dibatalkan karena masih ada produk yang memakai kategori tersebut.');
        }

        DB::table('categories')->whereIn('id', $categoryIds)->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Semua kategori berhasil dihapus.');
    }

    private function canManageCategories(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function abortIfCategoryOutsideStore(Category $category, ?string $storeName): void
    {
        if ($category->store_name !== null && $category->store_name !== $storeName) {
            abort(403, 'Anda tidak memiliki akses ke kategori ini.');
        }
    }

    private function makeUniqueSlug(string $name, ?Category $ignore = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
