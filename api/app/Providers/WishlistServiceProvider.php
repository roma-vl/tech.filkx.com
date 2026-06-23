<?php

namespace App\Providers;

use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;
use App\Services\WishlistService;
use Illuminate\Support\ServiceProvider;

class WishlistServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/wishlist.php'), 'wishlist'
        );

        $this->app->singleton(WishlistService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/wishlist.php'));

        if (config('wishlist.enabled', true)) {
            ProductVariant::observe(ProductVariantObserver::class);
        }
    }
}
