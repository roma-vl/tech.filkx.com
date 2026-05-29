<?php

use App\Api\Admin\Controllers\AdminAccountingController;
use App\Api\Admin\Controllers\AdminEmailController;
use App\Api\Admin\Controllers\AdminRoleController;
use App\Api\Admin\Controllers\AdminRunnerNodeController;
use App\Api\Admin\Controllers\AdminServerLogController;
use App\Api\Admin\Controllers\AdminSettingsController;
use App\Api\Admin\Controllers\AdminStatsController;
use App\Api\Admin\Controllers\AdminSupportController;
use App\Api\Admin\Controllers\AdminSupportSnippetController;
use App\Api\Admin\Controllers\AdminSystemController;
use App\Api\Admin\Controllers\AdminUserController;
use App\Api\Admin\Controllers\AdminProductController;
use App\Api\Admin\Controllers\AdminCategoryController;
use App\Api\Admin\Controllers\AdminBrandController;
use App\Api\Admin\Controllers\AdminAttributeController;
use App\Api\V1\Controllers\ActivityController;
use App\Api\V1\Controllers\Auth\AuthController;
use App\Api\V1\Controllers\Auth\OAuthController;
use App\Api\V1\Controllers\IndexController;
use App\Api\V1\Controllers\NotificationController;
use App\Api\V1\Controllers\SupportController;
use App\Api\V1\Controllers\SystemController;
use App\Api\V1\Controllers\CatalogController;
use App\Api\V1\Controllers\UserController;
use App\Http\Middleware\IdentifyImpersonation;
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

    // Catalog routes
    Route::prefix('catalog')->group(function () {
        Route::get('categories', [CatalogController::class, 'categories']);
        Route::get('brands', [CatalogController::class, 'brands']);
        Route::get('filters', [CatalogController::class, 'filters']);
        Route::get('products', [CatalogController::class, 'products']);
        Route::get('products/{slug}', [CatalogController::class, 'product']);
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
Route::middleware(['auth:api', IdentifyImpersonation::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth / Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/user/locale', [UserController::class, 'updateLocale']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);

    // Password management for OAuth users
    Route::post('/user/password/set', [UserController::class, 'setPassword']);

    // Avatar management
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/user/avatar', [UserController::class, 'deleteAvatar']);

    // User settings
    Route::post('/user/settings/referrer', [UserController::class, 'setReferrer']);
    Route::get('/user/settings/preferences', [UserController::class, 'getPreferences']);
    Route::put('/user/settings/preferences', [UserController::class, 'updatePreferences']);

    // Session management
    Route::get('/user/sessions', [UserController::class, 'sessions']);
    Route::post('/user/sessions/logout-all', [UserController::class, 'logoutAll']);

    // Account deletion
    Route::post('/user/delete', [UserController::class, 'initiateDelete']);

    /*
    |--------------------------------------------------------------------------
    | Activities
    |--------------------------------------------------------------------------
    */
    Route::get('/activities', [ActivityController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);



    Route::prefix('support')->group(function () {
        Route::get('/tickets', [SupportController::class, 'index']);
        Route::post('/tickets', [SupportController::class, 'store']);
        Route::get('/tickets/{ticket}', [SupportController::class, 'show']);
        Route::post('/tickets/{ticket}/message', [SupportController::class, 'sendMessage']);
        Route::post('/tickets/{ticket}/transfer', [SupportController::class, 'transfer']);
        Route::post('/tickets/{ticket}/transfer-to-ai', [SupportController::class, 'transferToAi']);
        Route::post('/tickets/{ticket}/mark-as-read', [SupportController::class, 'markAsRead']);
    });


    Route::prefix('admin')->middleware(['auth:api', IdentifyImpersonation::class, 'role:admin|administrator|moderator|support'])->group(function () {

        Route::get('stats', [AdminStatsController::class, 'index']);
        Route::get('system/health', [AdminSystemController::class, 'health']);

        Route::get('emails/campaigns', [AdminEmailController::class, 'index']);
        Route::post('emails/broadcast', [AdminEmailController::class, 'send']);
        Route::post('emails/preview', [AdminEmailController::class, 'preview']);

        Route::get('settings', [AdminSettingsController::class, 'index']);
        Route::post('settings', [AdminSettingsController::class, 'update']);
        Route::post('settings/watermark', [AdminSettingsController::class, 'uploadWatermark']);

        Route::get('support/tickets', [AdminSupportController::class, 'index']);
        Route::get('support/stats', [AdminSupportController::class, 'stats']);
        Route::get('support/users/{id}/tickets', [AdminSupportController::class, 'userTickets']);
        Route::get('support/tickets/{ticket}', [AdminSupportController::class, 'show']);
        Route::post('support/tickets/{ticket}/status', [AdminSupportController::class, 'updateStatus']);
        Route::post('support/tickets/{ticket}/tags', [AdminSupportController::class, 'updateTags']);
        Route::post('support/tickets/{ticket}/reply', [AdminSupportController::class, 'reply']);
        Route::get('support/tickets/{ticket}/messages', [AdminSupportController::class, 'messages']);
        Route::post('support/tickets/{ticket}/read-messages', [AdminSupportController::class, 'markMessagesAsRead']);
        Route::post('support/tickets/{ticket}/mark-as-read', [AdminSupportController::class, 'markAsRead']);
        Route::post('support/tickets/{ticket}/transfer-to-ai', [AdminSupportController::class, 'transferToAi']);
        Route::delete('support/tickets/{ticket}', [AdminSupportController::class, 'destroy']);

        // Support Snippets
        Route::apiResource('support/snippets', AdminSupportSnippetController::class);

        // User Management
        Route::get('users/plans', [AdminUserController::class, 'plans']);
        Route::get('users/export', [AdminUserController::class, 'export']);
        Route::get('users', [AdminUserController::class, 'index']);
        Route::post('users', [AdminUserController::class, 'store']);
        Route::put('users/{id}', [AdminUserController::class, 'update']);
        Route::post('users/{id}/suspend', [AdminUserController::class, 'suspend']);
        Route::delete('users/{id}', [AdminUserController::class, 'destroy']);

        // Roles & Permissions
        Route::get('roles', [AdminRoleController::class, 'index']);
        Route::post('roles', [AdminRoleController::class, 'store']);
        Route::put('roles/{id}', [AdminRoleController::class, 'update']);
        Route::delete('roles/{id}', [AdminRoleController::class, 'destroy']);
        Route::get('permissions', [AdminRoleController::class, 'permissions']);

        // Server Logs
        Route::get('server-logs', [AdminServerLogController::class, 'index']);
        Route::get('server-logs/{filename}', [AdminServerLogController::class, 'show']);
        Route::delete('server-logs/{filename}', [AdminServerLogController::class, 'clear']);

        // Product Management
        Route::get('products', [AdminProductController::class, 'index']);
        Route::post('products', [AdminProductController::class, 'store']);
        Route::put('products/{id}', [AdminProductController::class, 'update']);
        Route::delete('products/{id}', [AdminProductController::class, 'destroy']);
        Route::post('products/upload', [AdminProductController::class, 'uploadImage']);

        // Categories CRUD
        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);
        Route::put('categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy']);

        // Brands CRUD
        Route::get('brands', [AdminBrandController::class, 'index']);
        Route::post('brands', [AdminBrandController::class, 'store']);
        Route::put('brands/{id}', [AdminBrandController::class, 'update']);
        Route::delete('brands/{id}', [AdminBrandController::class, 'destroy']);

        // Attributes CRUD
        Route::get('attributes', [AdminAttributeController::class, 'index']);
        Route::post('attributes', [AdminAttributeController::class, 'store']);
        Route::put('attributes/{id}', [AdminAttributeController::class, 'update']);
        Route::delete('attributes/{id}', [AdminAttributeController::class, 'destroy']);

        // Accounting Module
    });

    Route::get('/version', [SystemController::class, 'status']);

});
