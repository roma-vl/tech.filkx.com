<?php

use App\Api\V1\Controllers\Auth\AuthController;
use App\Api\V1\Controllers\Auth\OAuthController;
use App\Api\V1\Controllers\IndexController;
use App\Api\V1\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/system/status', [SystemController::class, 'status']);


Route::prefix('v1')->group(function () {
// Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register',        [AuthController::class, 'register']);
        Route::post('/login',           [AuthController::class, 'login']);
        Route::get('/verify-email',     [AuthController::class, 'verifyEmailByParams']);
        Route::post('/email/resend',    [AuthController::class, 'resendVerification']);
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('/password/reset',  [AuthController::class, 'resetPassword']);

        // Protected auth routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/me',       [AuthController::class, 'me']);
            Route::post('/logout',  [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });
});

// OAuth routes
Route::prefix('oauth/{provider}')->group(function () {
    Route::get('/redirect',  [OAuthController::class, 'redirectToProvider']);
    Route::get('/callback',  [OAuthController::class, 'callback']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/connect',    [OAuthController::class, 'connect']);
        Route::delete('/disconnect', [OAuthController::class, 'disconnect']);
    });
});

// Other API routes
Route::get('/index', [IndexController::class, 'index']);
