<?php

use App\Api\V1\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// The 'api' middleware group is required (not applied automatically here, unlike
// routes/v1/api.php which gets it from bootstrap/app.php's withRouting(api: ...)) -
// without it, SubstituteBindings never runs and {product} never resolves to a model.
Route::prefix('api/v1/wishlist')
    ->middleware(['api', 'auth:api'])
    ->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/{product}', [WishlistController::class, 'add']);
        Route::delete('/{product}', [WishlistController::class, 'remove']);
        Route::patch('/{product}/notify', [WishlistController::class, 'toggleNotify']);
    });
