<?php

namespace App\Http\Controllers;

use App\Models\SalesForecast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(): View
    {
        return view('forecasts.index');
    }

    public function create(): View
    {
        return view('forecasts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('forecasts.index');
    }

    public function show(SalesForecast $forecast): View
    {
        return view('forecasts.show', ['forecast' => $forecast]);
    }

    public function edit(SalesForecast $forecast): View
    {
        return view('forecasts.edit', ['forecast' => $forecast]);
    }

    public function update(Request $request, SalesForecast $forecast): RedirectResponse
    {
        return redirect()->route('forecasts.index');
    }

    public function destroy(SalesForecast $forecast): RedirectResponse
    {
        return redirect()->route('forecasts.index');
    }
}
