<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->where('store_name', request()->user()?->store_name)
            ->orderByDesc('is_active')
            ->orderBy('nama_supplier')
            ->get();

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'activeSupplierCount' => $suppliers->where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'nama_kontak' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['store_name'] = $request->user()?->store_name;

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $this->abortIfSupplierOutsideStore($supplier, request()->user()?->store_name);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        $this->abortIfSupplierOutsideStore($supplier, request()->user()?->store_name);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->abortIfSupplierOutsideStore($supplier, $request->user()?->store_name);
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'nama_kontak' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->abortIfSupplierOutsideStore($supplier, request()->user()?->store_name);

        if (
            Product::query()->where('supplier_id', $supplier->id)->exists()
            || Purchase::query()->where('supplier_id', $supplier->id)->exists()
        ) {
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier tidak bisa dihapus karena sudah dipakai produk atau pembelian.');
        }

        try {
            $supplier->delete();
        } catch (QueryException $exception) {
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier tidak bisa dihapus karena sudah dipakai data pembelian.');
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $supplierIds = Supplier::query()
            ->where('store_name', $request->user()?->store_name)
            ->pluck('id');

        if ($supplierIds->isEmpty()) {
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Tidak ada supplier yang perlu dihapus.');
        }

        if (
            Product::query()->whereIn('supplier_id', $supplierIds)->exists()
            || Purchase::query()->whereIn('supplier_id', $supplierIds)->exists()
        ) {
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Hapus semua supplier dibatalkan karena masih ada produk atau pembelian yang memakai supplier tersebut.');
        }

        Supplier::query()->whereIn('id', $supplierIds)->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Semua supplier berhasil dihapus.');
    }

    private function abortIfSupplierOutsideStore(Supplier $supplier, ?string $storeName): void
    {
        if ($supplier->store_name !== null && $supplier->store_name !== $storeName) {
            abort(403, 'Anda tidak memiliki akses ke supplier ini.');
        }
    }
}
