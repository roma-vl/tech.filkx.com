<?php

namespace App\Providers;

use App\Api\V1\Repositories\ProductRepositoryInterface;
use App\Api\V1\Repositories\ProductRepository;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use App\Api\V1\Repositories\OrderRepository;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Api\V1\Repositories\CouponRepository;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Api\V1\Repositories\PromotionRepository;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(PromotionRepositoryInterface::class, PromotionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
