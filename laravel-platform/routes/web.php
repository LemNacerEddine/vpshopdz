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

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/pricing', function () {
    $plans = \App\Models\SubscriptionPlan::active()->get();
    return view('pricing', compact('plans'));
})->name('pricing');

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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ═══════════════════════════════════════════════════════════════
// DASHBOARD ROUTES (Authenticated - Blade + Alpine.js)
// ═══════════════════════════════════════════════════════════════

Route::middleware('auth')->prefix('dashboard')->name('dashboard')->group(function () {
    // Home
    Route::get('/', [DashboardController::class, 'index']);

    // Products & Categories
    Route::get('/products', [DashboardController::class, 'products'])->name('.products');
    Route::get('/categories', [DashboardController::class, 'categories'])->name('.categories');

    // Orders & Customers
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::get('/customers', [DashboardController::class, 'customers'])->name('.customers');

    // Shipping & Coupons
    Route::get('/shipping', [DashboardController::class, 'shipping'])->name('.shipping');
    Route::get('/coupons', [DashboardController::class, 'coupons'])->name('.coupons');

    // Reviews & Abandoned Carts
    Route::get('/reviews', [DashboardController::class, 'reviews'])->name('.reviews');
    Route::get('/abandoned-carts', [DashboardController::class, 'abandonedCarts'])->name('.abandoned-carts');

    // Themes & Domains & Pages
    Route::get('/themes', [DashboardController::class, 'themes'])->name('.themes');
    Route::get('/domains', [DashboardController::class, 'domains'])->name('.domains');
    Route::get('/pages', [DashboardController::class, 'pages'])->name('.pages');

    // Analytics & Marketing
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('.analytics');
    Route::get('/pixels', [DashboardController::class, 'pixels'])->name('.pixels');
    Route::get('/facebook-ads', [DashboardController::class, 'facebookAds'])->name('.facebook-ads');

    // Notifications
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('.notifications');

    // Subscription & Billing
    Route::get('/subscription', [DashboardController::class, 'subscription'])->name('.subscription');

    // Staff & Media
    Route::get('/staff', [DashboardController::class, 'staff'])->name('.staff');
    Route::get('/media', [DashboardController::class, 'media'])->name('.media');

    // Settings
    Route::get('/settings', [DashboardController::class, 'settings'])->name('.settings');
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
// STOREFRONT (Subdomain / Custom Domain / Slug → React)
// ═══════════════════════════════════════════════════════════════

// Direct slug route: /store/{slug} (for local development & testing)
Route::get('/store/{slug}', [StorefrontController::class, 'bySlug'])
    ->middleware('track.visitor')
    ->name('storefront.slug');

// Storefront sub-pages via slug: /store/{slug}/{path?}
Route::get('/store/{slug}/{path}', [StorefrontController::class, 'bySlug'])
    ->middleware('track.visitor')
    ->where('path', '.*');

// Fallback for subdomain / custom domain routing
Route::fallback([StorefrontController::class, 'index'])
    ->middleware('track.visitor');
