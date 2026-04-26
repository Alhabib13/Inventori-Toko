<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [AuthController::class, 'redirectAuthenticatedUser'])->name('home');
    Route::get('/pilih-mode-toko', [AuthController::class, 'showModeSelectionForm'])->name('mode-selection.show');
    Route::post('/pilih-mode-toko', [AuthController::class, 'storeModeSelection'])->name('mode-selection.store');

    Route::middleware(['role:owner', 'mode.selected'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.process');
        Route::resource('users', UserController::class);
        Route::resource('reports', ReportController::class)->only(['index']);
        Route::resource('forecasts', ForecastController::class);
    });

    Route::middleware(['role:owner,gudang', 'mode.selected'])->group(function (): void {
        Route::get('/stok', [StockController::class, 'index'])->name('stocks.role-home');
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('stocks', StockController::class);
        Route::resource('purchases', PurchaseController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    Route::middleware(['role:owner,kasir', 'mode.selected'])->group(function (): void {
        Route::resource('transactions', TransactionController::class);
    });

    Route::middleware('role:gudang')->group(function (): void {
        Route::get('/stok/notifikasi', [StockController::class, 'index'])->name('stocks.notifications');
    });

    Route::middleware('role:kasir')->group(function (): void {
        Route::get('/pos', [TransactionController::class, 'create'])->name('transactions.pos');
    });
});
