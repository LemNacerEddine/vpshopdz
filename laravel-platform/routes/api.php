<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SubscriptionController;

/*
|--------------------------------------------------------------------------
| VPShopDZ API Routes
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════
// PUBLIC ROUTES (بدون مصادقة)
// ═══════════════════════════════════════════════════════════════

Route::prefix('v1')->group(function () {

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public Store Data (للزبائن)
    Route::prefix('store/{storeId}')->group(function () {
        // المتجر
        Route::get('/', [StoreController::class, 'show']);
        
        // المنتجات
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/featured', [ProductController::class, 'featured']);
        Route::get('/products/on-sale', [ProductController::class, 'onSale']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        
        // الفئات
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        
        // الشحن
        Route::get('/wilayas', [ShippingController::class, 'wilayas']);
        Route::get('/communes/{wilayaId}', [ShippingController::class, 'communes']);
        Route::get('/shipping-rates', [ShippingController::class, 'rates']);
        
        // الطلبات (إنشاء من الزبون)
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{orderNumber}/track', [OrderController::class, 'track']);
        
        // التقييمات
        Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
        Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    });

    // Subscription Plans (عام)
    Route::get('/plans', [SubscriptionController::class, 'index']);

    // ═══════════════════════════════════════════════════════════════
    // PROTECTED ROUTES (تحتاج مصادقة)
    // ═══════════════════════════════════════════════════════════════

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'changePassword']);
        });

        // ═══════════════════════════════════════════════════════════════
        // STORE OWNER ROUTES (لوحة تحكم التاجر)
        // ═══════════════════════════════════════════════════════════════

        Route::prefix('dashboard')->middleware('role:store_owner,store_staff')->group(function () {

            // Dashboard Stats
            Route::get('/stats', [DashboardController::class, 'stats']);
            Route::get('/charts', [DashboardController::class, 'charts']);
            Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);

            // Store Settings
            Route::get('/store', [StoreController::class, 'current']);
            Route::put('/store', [StoreController::class, 'update']);
            Route::put('/store/settings', [StoreController::class, 'updateSettings']);

            // Products Management
            Route::apiResource('products', ProductController::class);
            Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate']);
            Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);
            Route::post('/products/bulk-update', [ProductController::class, 'bulkUpdate']);

            // Categories Management
            Route::apiResource('categories', CategoryController::class);
            Route::post('/categories/reorder', [CategoryController::class, 'reorder']);

            // Orders Management
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/stats', [OrderController::class, 'stats']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
            Route::post('/orders/{id}/notes', [OrderController::class, 'addNote']);
            Route::get('/orders/{id}/print', [OrderController::class, 'print']);

            // Customers
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::get('/customers/{id}', [CustomerController::class, 'show']);
            Route::get('/customers/{id}/orders', [CustomerController::class, 'orders']);

            // Shipping
            Route::get('/shipping/companies', [ShippingController::class, 'companies']);
            Route::put('/shipping/settings', [ShippingController::class, 'updateSettings']);
            Route::apiResource('shipping/rules', ShippingRuleController::class);

            // Coupons
            Route::apiResource('coupons', CouponController::class);

            // Reviews
            Route::get('/reviews', [ReviewController::class, 'storeIndex']);
            Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
            Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

            // Analytics
            Route::get('/analytics', [AnalyticsController::class, 'index']);
            Route::get('/analytics/products', [AnalyticsController::class, 'products']);
            Route::get('/analytics/customers', [AnalyticsController::class, 'customers']);

            // Abandoned Checkouts
            Route::get('/abandoned-checkouts', [AbandonedCheckoutController::class, 'index']);
            Route::post('/abandoned-checkouts/{id}/recover', [AbandonedCheckoutController::class, 'recover']);

            // Team (Staff)
            Route::apiResource('team', TeamController::class);

            // Subscription
            Route::get('/subscription', [SubscriptionController::class, 'current']);
            Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade']);
            Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
            Route::get('/subscription/invoices', [SubscriptionController::class, 'invoices']);

            // Upload
            Route::post('/upload', [UploadController::class, 'store']);
            Route::delete('/upload/{id}', [UploadController::class, 'destroy']);
        });

        // ═══════════════════════════════════════════════════════════════
        // SUPER ADMIN ROUTES (لوحة تحكم المنصة)
        // ═══════════════════════════════════════════════════════════════

        Route::prefix('admin')->middleware('role:super_admin')->group(function () {

            // Platform Stats
            Route::get('/stats', [AdminController::class, 'stats']);

            // Stores Management
            Route::get('/stores', [AdminController::class, 'stores']);
            Route::get('/stores/{id}', [AdminController::class, 'showStore']);
            Route::put('/stores/{id}/status', [AdminController::class, 'updateStoreStatus']);
            Route::delete('/stores/{id}', [AdminController::class, 'deleteStore']);

            // Users Management
            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{id}', [AdminController::class, 'updateUser']);

            // Subscription Plans
            Route::apiResource('plans', AdminPlanController::class);

            // Payments
            Route::get('/payments', [AdminController::class, 'payments']);
            Route::put('/payments/{id}/approve', [AdminController::class, 'approvePayment']);

            // Shipping Companies
            Route::apiResource('shipping-companies', AdminShippingController::class);
            Route::apiResource('shipping-rates', AdminShippingRateController::class);

            // Settings
            Route::get('/settings', [AdminController::class, 'settings']);
            Route::put('/settings', [AdminController::class, 'updateSettings']);
        });

    });

});
