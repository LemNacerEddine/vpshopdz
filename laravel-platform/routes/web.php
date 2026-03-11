<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\StorefrontController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Platform routes:
|   - Landing page (main domain)
|   - Dashboard (Blade + Alpine.js)
|   - Storefront (subdomain/custom domain → React)
|
*/

// ═══════════════════════════════════════════════════════════════
// MAIN PLATFORM DOMAIN
// ═══════════════════════════════════════════════════════════════

// Welcome / Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Pricing Page
Route::get('/pricing', function () {
    $plans = \App\Models\SubscriptionPlan::active()->get();
    return view('pricing', compact('plans'));
})->name('pricing');

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toISOString(),
    ]);
});

// ═══════════════════════════════════════════════════════════════
// AUTH ROUTES (Guest)
// ═══════════════════════════════════════════════════════════════

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ═══════════════════════════════════════════════════════════════
// DASHBOARD ROUTES (Authenticated - Blade + Alpine.js)
// ═══════════════════════════════════════════════════════════════

Route::middleware('auth')->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);

    // Products
    Route::get('/products', [DashboardController::class, 'products'])->name('.products');
    Route::post('/products', [DashboardController::class, 'storeProduct'])->name('.products.store');
    Route::put('/products/{product}', [DashboardController::class, 'updateProduct'])->name('.products.update');
    Route::delete('/products/{product}', [DashboardController::class, 'destroyProduct'])->name('.products.destroy');

    // Orders
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::put('/orders/{order}', [DashboardController::class, 'updateOrder'])->name('.orders.update');

    // Customers
    Route::get('/customers', function () {
        return view('dashboard.customers');
    })->name('.customers');

    // Shipping
    Route::get('/shipping', function () {
        return view('dashboard.shipping');
    })->name('.shipping');

    // Coupons
    Route::get('/coupons', function () {
        return view('dashboard.coupons');
    })->name('.coupons');

    // Reviews
    Route::get('/reviews', function () {
        return view('dashboard.reviews');
    })->name('.reviews');

    // Pages
    Route::get('/pages', function () {
        return view('dashboard.pages');
    })->name('.pages');

    // Abandoned Carts
    Route::get('/abandoned-carts', function () {
        return view('dashboard.abandoned-carts');
    })->name('.abandoned-carts');

    // Notifications
    Route::get('/notifications', function () {
        return view('dashboard.notifications');
    })->name('.notifications');

    // Pixels & Tracking
    Route::get('/pixels', function () {
        return view('dashboard.pixels');
    })->name('.pixels');

    // Facebook Ads
    Route::get('/facebook-ads', function () {
        return view('dashboard.facebook-ads');
    })->name('.facebook-ads');

    // Themes
    Route::get('/themes', function () {
        return view('dashboard.themes');
    })->name('.themes');

    // Domains
    Route::get('/domains', function () {
        return view('dashboard.domains');
    })->name('.domains');

    // Subscription
    Route::get('/subscription', function () {
        return view('dashboard.subscription');
    })->name('.subscription');

    // Analytics
    Route::get('/analytics', function () {
        return view('dashboard.analytics');
    })->name('.analytics');

    // Media Library
    Route::get('/media', function () {
        return view('dashboard.media');
    })->name('.media');

    // Staff
    Route::get('/staff', function () {
        return view('dashboard.staff');
    })->name('.staff');

    // Settings
    Route::get('/settings', [DashboardController::class, 'settings'])->name('.settings');
    Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('.settings.update');
});

// ═══════════════════════════════════════════════════════════════
// ADMIN ROUTES (Super Admin - Blade)
// ═══════════════════════════════════════════════════════════════

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('dashboard');

    Route::get('/stores', function () {
        return view('admin.stores');
    })->name('stores');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');

    Route::get('/themes', function () {
        return view('admin.themes');
    })->name('themes');

    Route::get('/plans', function () {
        return view('admin.plans');
    })->name('plans');

    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');
});

// ═══════════════════════════════════════════════════════════════
// STOREFRONT (Subdomain / Custom Domain → React)
// ═══════════════════════════════════════════════════════════════

// This catch-all route handles subdomain and custom domain resolution
// It should be the LAST route defined
Route::fallback([StorefrontController::class, 'index'])
    ->middleware('track.visitor');
