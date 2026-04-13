<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('suppliers.index');
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('suppliers.index');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        return redirect()->route('suppliers.index');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        return redirect()->route('suppliers.index');
    }
}
