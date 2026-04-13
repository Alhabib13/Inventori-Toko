<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return view('purchases.index');
    }

    public function create(): View
    {
        return view('purchases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('purchases.index');
    }

    public function show(Purchase $purchase): View
    {
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        return view('purchases.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        return redirect()->route('purchases.index');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        return redirect()->route('purchases.index');
    }
}
