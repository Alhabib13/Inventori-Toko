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
        $search = trim((string) $request->string('search'));

        $categoriesQuery = $this->scopeToUserStore(Category::query(), $request->user())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($categoryQuery) use ($search) {
                    $categoryQuery
                        ->where('nama_kategori', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            });

        $categorySummary = [
            'total' => (clone $categoriesQuery)->count(),
            'active' => (clone $categoriesQuery)->where('is_active', true)->count(),
            'inactive' => (clone $categoriesQuery)->where('is_active', false)->count(),
        ];

        $categories = (clone $categoriesQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            'categorySummary' => $categorySummary,
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
        $user = $request->user();
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')->where(fn ($query) => $this->scopeToUserStore($query, $user))],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug($validated['nama_kategori']);
        $validated['store_id'] = $user?->store_id;
        $validated['store_name'] = $user?->store_name;

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category): View
    {
        $this->abortIfCategoryOutsideStore($category, request()->user());
        return view('categories.show', [
            'category' => $category,
            'canManageCategories' => $this->canManageCategories(request()->user()?->role, request()->user()?->mode_app),
        ]);
    }

    public function edit(Category $category): View
    {
        $this->abortIfCategoryOutsideStore($category, request()->user());
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->abortIfCategoryOutsideStore($category, $request->user());
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')->where(fn ($query) => $this->scopeToUserStore($query, $request->user()))->ignore($category->id)],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug($validated['nama_kategori'], $category);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->abortIfCategoryOutsideStore($category, request()->user());

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
            ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
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

    private function abortIfCategoryOutsideStore(Category $category, $user): void
    {
        if (! $this->modelBelongsToUserStore($category, $user)) {
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
