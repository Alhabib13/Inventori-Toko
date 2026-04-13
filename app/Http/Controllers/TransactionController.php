<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        return view('transactions.index');
    }

    public function create(): View
    {
        return view('transactions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }

    public function show(Transaction $transaction): View
    {
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }
}
