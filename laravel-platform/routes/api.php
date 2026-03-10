<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\PlanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES (No Authentication Required)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('v1')->group(function () {
    
    // ─────────────────────────────────────────────────────────────────────────
    // Authentication
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // ─────────────────────────────────────────────────────────────────────────
    // Subscription Plans (Public)
    // ─────────────────────────────────────────────────────────────────────────
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{id}', [PlanController::class, 'show']);

    // ─────────────────────────────────────────────────────────────────────────
    // Public Store Routes (Storefront)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('store/{store}')->group(function () {
        // Store info
        Route::get('/', [StoreController::class, 'show']);
        
        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/featured', [ProductController::class, 'featured']);
        Route::get('/products/on-sale', [ProductController::class, 'onSale']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        
        // Categories
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        
        // Shipping
        Route::get('/wilayas', [ShippingController::class, 'wilayas']);
        Route::get('/communes/{wilayaId}', [ShippingController::class, 'communes']);
        Route::get('/shipping-rates', [ShippingController::class, 'rates']);
        
        // Orders (Customer)
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{orderNumber}/track', [OrderController::class, 'track']);
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED ROUTES (Authentication Required)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // ─────────────────────────────────────────────────────────────────────────
    // Auth (Protected)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
    });

    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard Routes (Store Owner)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('dashboard')->group(function () {
        // Overview
        Route::get('/', [StoreController::class, 'dashboard']);
        Route::get('/analytics', [StoreController::class, 'analytics']);
        
        // Store Settings
        Route::get('/settings', [StoreController::class, 'settings']);
        Route::put('/settings', [StoreController::class, 'updateSettings']);
        
        // Products
        Route::get('/products', [ProductController::class, 'dashboardIndex']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate']);
        
        // Categories
        Route::get('/categories', [CategoryController::class, 'dashboardIndex']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        
        // Orders
        Route::get('/orders', [OrderController::class, 'dashboardIndex']);
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        
        // Customers
        Route::get('/customers', [StoreController::class, 'customers']);
        
        // Shipping
        Route::get('/shipping/companies', [ShippingController::class, 'companies']);
        Route::put('/shipping/settings', [ShippingController::class, 'updateSettings']);
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// ADMIN ROUTES (Platform Admin Only)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
    // TODO: Admin routes for platform management
    Route::get('/stores', function () {
        return response()->json(['message' => 'Admin stores endpoint']);
    });
});
