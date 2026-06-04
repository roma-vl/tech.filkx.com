<?php

use App\Api\Admin\Controllers\AdminAccountingController;
use App\Api\Admin\Controllers\AdminAttributeController;
use App\Api\Admin\Controllers\AdminBrandController;
use App\Api\Admin\Controllers\AdminCategoryController;
use App\Api\Admin\Controllers\AdminMarketingController;
use App\Api\Admin\Controllers\AdminOrderController;
use App\Api\Admin\Controllers\AdminProductController;
use App\Api\Admin\Controllers\AdminRoleController;
use App\Api\Admin\Controllers\AdminServerLogController;
use App\Api\Admin\Controllers\AdminSettingsController;
use App\Api\Admin\Controllers\AdminStatsController;
use App\Api\Admin\Controllers\AdminSupportController;
use App\Api\Admin\Controllers\AdminSupportSnippetController;
use App\Api\Admin\Controllers\AdminSystemController;
use App\Api\Admin\Controllers\AdminNotificationController;
use App\Api\Admin\Controllers\AdminUserController;
use App\Api\V1\Controllers\ActivityController;
use App\Api\V1\Controllers\Auth\AuthController;
use App\Api\V1\Controllers\Auth\OAuthController;
use App\Api\V1\Controllers\CartController;
use App\Api\V1\Controllers\CatalogController;
use App\Api\V1\Controllers\CheckoutController;
use App\Api\V1\Controllers\CouponController;
use App\Api\V1\Controllers\HomeController;
use App\Api\V1\Controllers\IndexController;
use App\Api\V1\Controllers\NotificationController;
use App\Api\V1\Controllers\SupportController;
use App\Api\V1\Controllers\SystemController;
use App\Api\V1\Controllers\UserController;
use App\Http\Middleware\IdentifyImpersonation;
use Illuminate\Support\Facades\Route;

Route::get('/system/status', [SystemController::class, 'status']);

Route::prefix('v1')->group(function () {
    // Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/verify-email', [AuthController::class, 'verifyEmailByParams']);
        Route::post('/email/resend', [AuthController::class, 'resendVerification']);
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('/password/reset', [AuthController::class, 'resetPassword']);

        // Protected auth routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

    // Catalog routes
    Route::prefix('catalog')->group(function () {
        Route::get('home', [HomeController::class, 'homeData']);
        Route::get('categories', [CatalogController::class, 'categories']);
        Route::get('brands', [CatalogController::class, 'brands']);
        Route::get('filters', [CatalogController::class, 'filters']);
        Route::get('products', [CatalogController::class, 'products']);
        Route::get('products/random', [CatalogController::class, 'randomProducts']);
        Route::get('products/{slug}', [CatalogController::class, 'product']);
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/', [CartController::class, 'add']);
        Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
        Route::post('/merge', [CartController::class, 'merge'])->middleware('auth:api');
    });

    // Checkout route
    Route::post('/checkout', [CheckoutController::class, 'placeOrder']);
    Route::post('/checkout/quick', [CheckoutController::class, 'quickOrder']);
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
});

// OAuth routes
Route::prefix('oauth/{provider}')->group(function () {
    Route::get('/redirect', [OAuthController::class, 'redirectToProvider']);
    Route::get('/callback', [OAuthController::class, 'callback']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/connect', [OAuthController::class, 'connect']);
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
    Route::get('/user/settings/preferences', [UserController::class, 'getPreferences']);
    Route::put('/user/settings/preferences', [UserController::class, 'updatePreferences']);

    // Favorites (wishlist) database-backed endpoints
    Route::get('/user/favorites', [UserController::class, 'getFavorites']);
    Route::post('/user/favorites/toggle', [UserController::class, 'toggleFavorite']);
    Route::post('/user/favorites/sync', [UserController::class, 'syncFavorites']);
    
    // Compare database-backed endpoints
    Route::get('/user/compares', [UserController::class, 'getCompares']);
    Route::post('/user/compares/toggle', [UserController::class, 'toggleCompare']);
    Route::post('/user/compares/sync', [UserController::class, 'syncCompares']);
    
    // Viewed products history database-backed endpoints
    Route::get('/user/viewed-products', [UserController::class, 'getViewedProducts']);
    Route::post('/user/viewed-products/track', [UserController::class, 'trackViewedProduct']);
    Route::post('/user/viewed-products/sync', [UserController::class, 'syncViewedProducts']);
    Route::delete('/user/viewed-products/clear', [UserController::class, 'clearViewedProducts']);

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
        Route::get('analytics/overview', [AdminStatsController::class, 'overview']);
        Route::get('analytics/charts', [AdminStatsController::class, 'charts']);
        Route::get('analytics/distributions', [AdminStatsController::class, 'distributions']);
        Route::get('system/health', [AdminSystemController::class, 'health']);

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

        // Order Management
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{id}', [AdminOrderController::class, 'show']);
        Route::put('orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
        Route::delete('orders/{id}', [AdminOrderController::class, 'destroy']);

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
        Route::get('accounting/ledger', [AdminAccountingController::class, 'ledger']);
        Route::get('accounting/invoices', [AdminAccountingController::class, 'invoices']);
        Route::get('accounting/stats', [AdminAccountingController::class, 'accountingStats']);
        Route::get('accounting/export', [AdminAccountingController::class, 'export']);

        Route::get('billing/stats', [AdminAccountingController::class, 'billingStats']);
        Route::get('billing/payments/pending', [AdminAccountingController::class, 'pendingPayments']);
        Route::post('billing/payments/{id}/confirm', [AdminAccountingController::class, 'confirmPayment']);
        Route::get('billing/payments/{id}/proof', [AdminAccountingController::class, 'viewProof']);
        Route::get('billing/subscriptions', [AdminAccountingController::class, 'subscriptions']);

        // Marketing Module
        Route::get('marketing/coupons', [AdminMarketingController::class, 'coupons']);
        Route::post('marketing/coupons', [AdminMarketingController::class, 'storeCoupon']);
        Route::put('marketing/coupons/{id}', [AdminMarketingController::class, 'updateCoupon']);
        Route::delete('marketing/coupons/{id}', [AdminMarketingController::class, 'destroyCoupon']);

        Route::get('marketing/promotions', [AdminMarketingController::class, 'promotions']);
        Route::post('marketing/promotions', [AdminMarketingController::class, 'storePromotion']);
        Route::put('marketing/promotions/{id}', [AdminMarketingController::class, 'updatePromotion']);
        Route::delete('marketing/promotions/{id}', [AdminMarketingController::class, 'destroyPromotion']);

        // Notifications CRUD
        Route::get('notifications', [AdminNotificationController::class, 'index']);
        Route::post('notifications', [AdminNotificationController::class, 'store']);
        Route::post('notifications/broadcast', [AdminNotificationController::class, 'broadcast']);
        Route::delete('notifications/{id}', [AdminNotificationController::class, 'destroy']);
    });

    Route::get('/version', [SystemController::class, 'status']);

});
