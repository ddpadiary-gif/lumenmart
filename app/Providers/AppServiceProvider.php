<?php

namespace App\Providers;

use App\Models\Product;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'id'));

        // Paksa HTTPS saat production atau di balik proxy/tunnel HTTPS (Cloudflare, Pinggy, hosting gratis)
        // agar aset CSS/JS tidak pernah terkena Mixed Content.
        if ($this->app->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Lonceng notifikasi stok kritis pada layout back-office.
        View::composer('layouts.admin', function ($view) {
            $view->with('criticalProducts', Product::query()
                ->with('category:id,name')
                ->active()
                ->critical()
                ->orderBy('current_stock')
                ->get(['id', 'category_id', 'name', 'sku', 'current_stock', 'min_stock_threshold', 'unit']));
        });

        // Jumlah item keranjang pada navbar storefront (dan meta tag untuk store Alpine AJAX).
        View::composer(['layouts.navigation', 'layouts.app'], function ($view) {
            $user = auth()->user();
            $view->with('cartCount', $user?->isCustomer() ? app(CartService::class)->count() : 0);
        });
    }
}
