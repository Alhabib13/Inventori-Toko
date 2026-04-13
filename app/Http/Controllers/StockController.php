<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        return view('stocks.index');
    }

    public function create(): View
    {
        return view('stocks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('stocks.index');
    }

    public function show(StockMovement $stock): View
    {
        return view('stocks.show', ['stock' => $stock]);
    }

    public function edit(StockMovement $stock): View
    {
        return view('stocks.edit', ['stock' => $stock]);
    }

    public function update(Request $request, StockMovement $stock): RedirectResponse
    {
        return redirect()->route('stocks.index');
    }

    public function destroy(StockMovement $stock): RedirectResponse
    {
        return redirect()->route('stocks.index');
    }
}
