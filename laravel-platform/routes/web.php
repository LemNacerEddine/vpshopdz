<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toISOString(),
    ]);
});

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard Routes (Authenticated)
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
    
    // Settings
    Route::get('/settings', [DashboardController::class, 'settings'])->name('.settings');
    Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('.settings.update');
});
