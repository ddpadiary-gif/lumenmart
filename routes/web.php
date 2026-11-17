<?php

use App\Http\Controllers\Backoffice\AprioriController;
use App\Http\Controllers\Backoffice\CategoryController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\EcommerceOrderController;
use App\Http\Controllers\Backoffice\IncomingTransactionController;
use App\Http\Controllers\Backoffice\PosController;
use App\Http\Controllers\Backoffice\ProductController;
use App\Http\Controllers\Backoffice\RegressionController;
use App\Http\Controllers\Backoffice\ReportController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// Storefront Publik (Katalog & Paket Bundling)
Route::get('/', [StorefrontController::class, 'index'])->name('home');

// Redirect otomatis setelah login berdasarkan role
Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth'])
    ->name('dashboard');

// Fitur Pelanggan (Customer E-Commerce)
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/bundle/{rule}', [CartController::class, 'addBundle'])->name('cart.bundle');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
});

// Unduh Invoice PDF (Customer pemilik pesanan, Admin, atau Pemilik)
Route::get('/invoices/{order}/pdf', InvoiceController::class)
    ->middleware(['auth'])
    ->name('invoices.pdf');

// Back-Office: Akses bersama Admin & Pemilik (Read-Only Analitik & Laporan untuk Pemilik)
Route::middleware(['auth', 'role:admin,pemilik'])
    ->prefix('backoffice')
    ->name('backoffice.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/analytics/regression', [RegressionController::class, 'index'])->name('regression.index');
        Route::get('/analytics/apriori', [AprioriController::class, 'index'])->name('apriori.index');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales-csv', [ReportController::class, 'exportSalesCsv'])->name('reports.sales.csv');
        Route::get('/reports/inventory-csv', [ReportController::class, 'exportInventoryCsv'])->name('reports.inventory.csv');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    });

// Back-Office: Akses Eksklusif Admin (Operasional, CRUD, POS, Restock, Eksekusi ML & Reset Simulasi)
Route::middleware(['auth', 'role:admin'])
    ->prefix('backoffice')
    ->name('backoffice.')
    ->group(function () {
        Route::post('/simulation/reset', [DashboardController::class, 'resetSimulation'])->name('simulation.reset');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('/incoming', [IncomingTransactionController::class, 'index'])->name('incoming.index');
        Route::post('/incoming', [IncomingTransactionController::class, 'store'])->name('incoming.store');

        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

        Route::get('/orders', [EcommerceOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/status', [EcommerceOrderController::class, 'updateStatus'])->name('orders.status');

        Route::post('/analytics/regression/generate', [RegressionController::class, 'generate'])->name('regression.generate');
        Route::post('/analytics/apriori/generate', [AprioriController::class, 'generate'])->name('apriori.generate');
    });

// Profil Pengguna (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
