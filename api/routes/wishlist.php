<?php

use App\Api\V1\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/wishlist')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/',                   [WishlistController::class, 'index']);
        Route::post('/{product}',         [WishlistController::class, 'add']);
        Route::delete('/{product}',       [WishlistController::class, 'remove']);
        Route::patch('/{product}/notify', [WishlistController::class, 'toggleNotify']);
    });
