<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PixelController;
use App\Http\Controllers\Api\FacebookAdController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\AbandonedCartController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CustomerAuthController;

/*
|--------------------------------------------------------------------------
| VPShopDZ API Routes v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ═══════════════════════════════════════════════════════════════
    // 1. PUBLIC ROUTES
    // ═══════════════════════════════════════════════════════════════

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::post('/staff/accept-invitation', [StaffController::class, 'acceptInvitation']);
    Route::get('/plans', [SubscriptionController::class, 'plans']);
    Route::get('/themes', [ThemeController::class, 'catalog']);
    Route::get('/themes/{id}', [ThemeController::class, 'show']);
    Route::get('/themes/{id}/preview', [ThemeController::class, 'preview']);

    // ═══════════════════════════════════════════════════════════════
    // 2. STOREFRONT ROUTES (Public - per store)
    // ═══════════════════════════════════════════════════════════════

    Route::prefix('store/{store}')->group(function () {

        // Store info
        Route::get('/', [StoreController::class, 'show']);
        Route::get('/settings', [StoreController::class, 'publicSettings']);

        // Products - specific routes BEFORE parameterized routes
        Route::get('/products/featured', [ProductController::class, 'featured']);
        Route::get('/products/new', [ProductController::class, 'newArrivals']);
        Route::get('/products/sale', [ProductController::class, 'onSale']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}/related', [ProductController::class, 'related']);
        Route::get('/products/{product}', [ProductController::class, 'show']);

        // Categories
        Route::get('/categories', [ProductController::class, 'categories']);
        Route::get('/categories/{category}/products', [ProductController::class, 'categoryProducts']);
        Route::get('/categories/{category}', [ProductController::class, 'categoryShow']);

        // Reviews
        Route::get('/products/{product}/reviews', [ReviewController::class, 'productReviews']);
        Route::post('/products/{product}/reviews', [ReviewController::class, 'submitReview']);

        // Orders
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{tracking}', [OrderController::class, 'trackByNumber']);
        Route::post('/orders/track', [OrderController::class, 'track']);

        // Shipping
        Route::get('/shipping/wilayas', [ShippingController::class, 'wilayas']);
        Route::get('/shipping/communes/{wilaya}', [ShippingController::class, 'communes']);
        Route::post('/shipping/calculate', [ShippingController::class, 'calculate']);
        Route::get('/shipping/rates', [ShippingController::class, 'rates']);
        Route::get('/shipping/companies', [ShippingController::class, 'publicCompanies']);

        // Coupons
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

        // Cart (Session-based)
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::post('/cart/sync', [CartController::class, 'sync']);

        // Wishlist (Session-based)
        Route::get('/wishlist', [WishlistController::class, 'show']);
        Route::post('/wishlist/items', [WishlistController::class, 'addItem']);
        Route::delete('/wishlist/items/{productId}', [WishlistController::class, 'removeItem']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

        // Pages
        Route::get('/pages', [PageController::class, 'storePages']);
        Route::get('/pages/{slug}', [PageController::class, 'storePage']);

        // Pixels
        Route::get('/pixels', [PixelController::class, 'storePixels']);

        // Customer Auth (Storefront)
        Route::prefix('customer')->group(function () {
            Route::post('/register', [CustomerAuthController::class, 'register']);
            Route::post('/login', [CustomerAuthController::class, 'login']);
            Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword']);
            Route::middleware('auth:customer')->group(function () {
                Route::post('/logout', [CustomerAuthController::class, 'logout']);
                Route::get('/profile', [CustomerAuthController::class, 'profile']);
                Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
                Route::put('/password', [CustomerAuthController::class, 'changePassword']);
                Route::get('/orders', [CustomerAuthController::class, 'myOrders']);
            });
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // 3. DASHBOARD ROUTES (Authenticated)
    // ═══════════════════════════════════════════════════════════════

    Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);

        // Store
        Route::get('/store', [StoreController::class, 'dashboard']);
        Route::post('/store', [StoreController::class, 'create']);
        Route::put('/store', [StoreController::class, 'update']);
        Route::get('/store/settings', [StoreController::class, 'dashboardSettings']);
        Route::put('/store/settings', [StoreController::class, 'updateSettings']);

        // Products
        Route::get('/products', [ProductController::class, 'dashboardIndex']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{id}', [ProductController::class, 'dashboardShow']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate']);
        Route::post('/products/bulk-action', [ProductController::class, 'bulkAction']);
        Route::get('/products-export', [ProductController::class, 'export']);

        // Categories
        Route::get('/categories', [ProductController::class, 'dashboardCategories']);
        Route::post('/categories', [ProductController::class, 'createCategory']);
        Route::put('/categories/{id}', [ProductController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [ProductController::class, 'deleteCategory']);

        // Orders
        Route::get('/orders', [OrderController::class, 'dashboardIndex']);
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/notes', [OrderController::class, 'updateNotes']);
        Route::post('/orders/{id}/duplicate', [OrderController::class, 'duplicate']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
        Route::post('/orders/bulk-status', [OrderController::class, 'bulkUpdateStatus']);
        Route::get('/orders-export', [OrderController::class, 'export']);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/stats', [CustomerController::class, 'stats']);
        Route::get('/customers/export', [CustomerController::class, 'export']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

        // Shipping
        Route::get('/shipping/companies', [ShippingController::class, 'companies']);
        Route::post('/shipping/companies', [ShippingController::class, 'createCompany']);
        Route::put('/shipping/companies/{id}', [ShippingController::class, 'updateCompany']);
        Route::delete('/shipping/companies/{id}', [ShippingController::class, 'deleteCompany']);
        Route::get('/shipping/rates', [ShippingController::class, 'dashboardRates']);
        Route::post('/shipping/rates', [ShippingController::class, 'createRate']);
        Route::put('/shipping/rates/{id}', [ShippingController::class, 'updateRate']);
        Route::delete('/shipping/rates/{id}', [ShippingController::class, 'deleteRate']);
        Route::post('/shipping/rates/import', [ShippingController::class, 'importRates']);
        Route::get('/shipping/rules', [ShippingController::class, 'rules']);
        Route::post('/shipping/rules', [ShippingController::class, 'createRule']);
        Route::put('/shipping/rules/{id}', [ShippingController::class, 'dashboardUpdateRule']);
        Route::delete('/shipping/rules/{id}', [ShippingController::class, 'deleteRule']);
        Route::get('/shipping/settings', [ShippingController::class, 'dashboardGetSettings']);
        Route::put('/shipping/settings', [ShippingController::class, 'dashboardUpdateSettings']);

        // Coupons
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::post('/coupons', [CouponController::class, 'store']);
        Route::get('/coupons/{id}', [CouponController::class, 'show']);
        Route::put('/coupons/{id}', [CouponController::class, 'update']);
        Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
        Route::put('/coupons/{id}/toggle', [CouponController::class, 'toggle']);

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
        Route::put('/reviews/{id}/reply', [ReviewController::class, 'reply']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

        // Pages
        Route::get('/pages', [PageController::class, 'index']);
        Route::post('/pages', [PageController::class, 'store']);
        Route::put('/pages/reorder', [PageController::class, 'reorder']);
        Route::put('/pages/{id}', [PageController::class, 'update']);
        Route::delete('/pages/{id}', [PageController::class, 'destroy']);

        // Abandoned Carts
        Route::get('/abandoned-carts', [AbandonedCartController::class, 'index']);
        Route::get('/abandoned-carts/stats', [AbandonedCartController::class, 'stats']);
        Route::post('/abandoned-carts/{id}/recover', [AbandonedCartController::class, 'sendRecovery']);
        Route::delete('/abandoned-carts/{id}', [AbandonedCartController::class, 'destroy']);

        // Notifications
        Route::get('/notifications/templates', [NotificationController::class, 'templates']);
        Route::post('/notifications/templates', [NotificationController::class, 'createTemplate']);
        Route::put('/notifications/templates/{id}', [NotificationController::class, 'updateTemplate']);
        Route::delete('/notifications/templates/{id}', [NotificationController::class, 'deleteTemplate']);
        Route::post('/notifications/send', [NotificationController::class, 'send']);
        Route::get('/notifications/settings', [NotificationController::class, 'settings']);
        Route::put('/notifications/settings', [NotificationController::class, 'updateSettings']);
        Route::post('/notifications/test', [NotificationController::class, 'testNotification']);

        // Pixels
        Route::get('/pixels', [PixelController::class, 'index']);
        Route::post('/pixels', [PixelController::class, 'store']);
        Route::put('/pixels/{id}', [PixelController::class, 'update']);
        Route::delete('/pixels/{id}', [PixelController::class, 'destroy']);
        Route::put('/pixels/{id}/toggle', [PixelController::class, 'toggle']);

        // Facebook Ads
        Route::get('/facebook-ads', [FacebookAdController::class, 'index']);
        Route::post('/facebook-ads', [FacebookAdController::class, 'store']);
        Route::get('/facebook-ads/{id}', [FacebookAdController::class, 'show']);
        Route::put('/facebook-ads/{id}', [FacebookAdController::class, 'update']);
        Route::put('/facebook-ads/{id}/pause', [FacebookAdController::class, 'pause']);
        Route::put('/facebook-ads/{id}/resume', [FacebookAdController::class, 'resume']);
        Route::delete('/facebook-ads/{id}', [FacebookAdController::class, 'destroy']);

        // Themes
        Route::get('/themes', [ThemeController::class, 'myThemes']);
        Route::post('/themes/install', [ThemeController::class, 'install']);
        Route::put('/themes/active', [ThemeController::class, 'setActive']);
        Route::put('/themes/{id}/customize', [ThemeController::class, 'customize']);
        Route::delete('/themes/{id}', [ThemeController::class, 'uninstall']);

        // Domains
        Route::get('/domains', [DomainController::class, 'index']);
        Route::post('/domains', [DomainController::class, 'store']);
        Route::put('/domains/{id}/verify', [DomainController::class, 'verify']);
        Route::put('/domains/{id}/primary', [DomainController::class, 'setPrimary']);
        Route::delete('/domains/{id}', [DomainController::class, 'destroy']);

        // Subscription
        Route::get('/subscription', [SubscriptionController::class, 'current']);
        Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade']);
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/subscription/invoices', [SubscriptionController::class, 'invoices']);

        // Analytics
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/analytics/orders-status', [AnalyticsController::class, 'ordersByStatus']);
        Route::get('/analytics/top-products', [AnalyticsController::class, 'topProducts']);
        Route::get('/analytics/by-wilaya', [AnalyticsController::class, 'byWilaya']);
        Route::get('/analytics/traffic', [AnalyticsController::class, 'traffic']);

        // Media
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::delete('/media', [MediaController::class, 'destroy']);

        // Staff
        Route::middleware('role:merchant')->group(function () {
            Route::get('/staff', [StaffController::class, 'index']);
            Route::post('/staff/invite', [StaffController::class, 'invite']);
            Route::put('/staff/{id}/permissions', [StaffController::class, 'updatePermissions']);
            Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
            Route::delete('/staff/invitations/{id}', [StaffController::class, 'cancelInvitation']);
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // 4. ADMIN ROUTES
    // ═══════════════════════════════════════════════════════════════

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/stores', [AdminController::class, 'stores']);
        Route::get('/stores/{id}', [AdminController::class, 'storeDetails']);
        Route::put('/stores/{id}/status', [AdminController::class, 'updateStoreStatus']);
        Route::put('/stores/{id}/plan', [AdminController::class, 'changeStorePlan']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}/ban', [AdminController::class, 'toggleBan']);
        Route::post('/themes', [AdminController::class, 'createTheme']);
        Route::put('/themes/{id}', [AdminController::class, 'updateTheme']);
        Route::delete('/themes/{id}', [AdminController::class, 'deleteTheme']);
        Route::get('/settings', [AdminController::class, 'settings']);
        Route::get('/plans', [SubscriptionController::class, 'adminPlans']);
        Route::post('/plans', [SubscriptionController::class, 'createPlan']);
        Route::put('/plans/{id}', [SubscriptionController::class, 'updatePlan']);
        Route::delete('/plans/{id}', [SubscriptionController::class, 'deletePlan']);
    });
});
