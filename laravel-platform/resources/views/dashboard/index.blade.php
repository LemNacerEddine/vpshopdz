@extends('layouts.dashboard')

@section('title', 'الرئيسية')

@section('content')
<div class="space-y-6">
    <!-- Welcome & Store Status -->
    <div class="bg-gradient-to-l from-primary-600 to-primary-700 rounded-3xl p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full translate-x-1/2 translate-y-1/2"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black mb-2">مرحباً، {{ auth()->user()->name }} 👋</h1>
                <p class="text-white/80">إليك ملخص أداء متجرك اليوم</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="#" class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl hover:bg-white/30 transition-all flex items-center gap-2" data-testid="view-store-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    معاينة المتجر
                </a>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content - Order Status Overview -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg transition-all" data-testid="stat-orders-today">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-green-500 font-bold bg-green-50 px-2 py-1 rounded-lg">+12%</span>
                    </div>
                    <p class="text-3xl font-black text-gray-800">{{ $stats['today_orders'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">طلبات اليوم</p>
                </div>
                
                <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg transition-all" data-testid="stat-revenue-today">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-green-500 font-bold bg-green-50 px-2 py-1 rounded-lg">+8%</span>
                    </div>
                    <p class="text-3xl font-black text-gray-800">{{ number_format($stats['today_revenue'] ?? 0) }}</p>
                    <p class="text-sm text-gray-500">مبيعات اليوم (د.ج)</p>
                </div>
                
                <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg transition-all" data-testid="stat-products">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-gray-800">{{ $stats['products_count'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">إجمالي المنتجات</p>
                </div>
                
                <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg transition-all" data-testid="stat-pending">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        @if(($stats['pending_orders'] ?? 0) > 0)
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                        @endif
                    </div>
                    <p class="text-3xl font-black text-gray-800">{{ $stats['pending_orders'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">طلبات بانتظار التأكيد</p>
                </div>
            </div>

            <!-- Order Status Summary Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        ملخص حالات الطلبات
                    </h2>
                    <a href="{{ route('dashboard.orders') }}" class="text-primary-600 text-sm font-medium hover:underline" data-testid="view-all-orders">
                        عرض الكل
                    </a>
                </div>
                
                <div class="divide-y divide-gray-50">
                    <!-- Pending Orders -->
                    <a href="{{ route('dashboard.orders', ['status' => 'pending']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-pending">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-amber-100 text-amber-700">
                                    <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                    جديدة
                                </span>
                            </div>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['pending'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['pending_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- No Answer 1 -->
                    <a href="{{ route('dashboard.orders', ['status' => 'no_answer_1']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-no-answer-1">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                لم يرد على الإتصال...1
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['no_answer_1'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['no_answer_1_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Confirmed -->
                    <a href="{{ route('dashboard.orders', ['status' => 'confirmed']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-confirmed">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                مؤكدة
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['confirmed'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['confirmed_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Ready -->
                    <a href="{{ route('dashboard.orders', ['status' => 'ready']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-ready">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-teal-100 text-teal-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                جاهزة
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['ready'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['ready_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Shipped -->
                    <a href="{{ route('dashboard.orders', ['status' => 'shipped']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-shipped">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                في الطريق للزبون
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['shipped'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['shipped_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Delivered -->
                    <a href="{{ route('dashboard.orders', ['status' => 'delivered']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-delivered">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                تم التسليم للزبون
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['delivered'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['delivered_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Cancelled -->
                    <a href="{{ route('dashboard.orders', ['status' => 'cancelled']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-cancelled">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                ملغاة
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['cancelled'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['cancelled_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                    
                    <!-- Returned -->
                    <a href="{{ route('dashboard.orders', ['status' => 'returned']) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors" data-testid="status-returned">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                مرجعة
                            </span>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-black text-gray-800">{{ $stats['returned'] ?? 0 }}</p>
                            <p class="text-sm text-gray-400">{{ number_format($stats['returned_value'] ?? 0) }} د.ج</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar - Setup & Quick Actions -->
        <div class="space-y-6">
            <!-- Store Setup Progress -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-primary-500 to-primary-600 px-6 py-4">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        أكمل تحضير متجرك
                    </h3>
                    <p class="text-white/70 text-sm mt-1">{{ $setupProgress ?? 2 }}/5 مكتمل</p>
                    <div class="mt-3 h-2 bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full transition-all" style="width: {{ (($setupProgress ?? 2) / 5) * 100 }}%"></div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-50">
                    <!-- Add Logo -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors cursor-pointer" data-testid="setup-logo">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm">أضف شعار المتجر</p>
                            <p class="text-xs text-gray-400">مكتمل</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </div>
                    
                    <!-- Add Products -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors cursor-pointer" data-testid="setup-products">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm">أضف منتجات</p>
                            <p class="text-xs text-gray-400">مكتمل</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </div>
                    
                    <!-- Set Shipping Prices -->
                    <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-4 px-5 py-4 hover:bg-primary-50 transition-colors group" data-testid="setup-shipping">
                        <div class="w-10 h-10 bg-gray-100 group-hover:bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm group-hover:text-primary-600">حدد أسعار ولايات التوصيل</p>
                            <p class="text-xs text-gray-400">غير مكتمل</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    
                    <!-- Connect Telegram -->
                    <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-primary-50 transition-colors group" data-testid="setup-telegram">
                        <div class="w-10 h-10 bg-gray-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm group-hover:text-primary-600">اربط حسابك على Telegram</p>
                            <p class="text-xs text-gray-400">لتلقي إشعارات الطلبات الجديدة</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    
                    <!-- Add Contact Methods -->
                    <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-4 px-5 py-4 hover:bg-primary-50 transition-colors group" data-testid="setup-contact">
                        <div class="w-10 h-10 bg-gray-100 group-hover:bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm group-hover:text-primary-600">أضف وسائل الاتصال لزبائنك</p>
                            <p class="text-xs text-gray-400">غير مكتمل</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Mobile App Promo -->
            <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-5 text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-primary-500/20 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-black">VPShop Scan</p>
                            <p class="text-xs text-gray-400">تطبيق المتجر</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-300 mb-4">حمّل التطبيق لإدارة طلباتك بسهولة من هاتفك</p>
                    <a href="#" class="inline-flex items-center gap-2 bg-primary-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-primary-700 transition-colors" data-testid="download-app-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        حمّل التطبيق
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    إجراءات سريعة
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('dashboard.products') }}" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-primary-50 hover:text-primary-600 transition-all group" data-testid="quick-add-product">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:bg-primary-100">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-sm">إضافة منتج جديد</span>
                    </a>
                    <a href="{{ route('dashboard.orders') }}" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-primary-50 hover:text-primary-600 transition-all group" data-testid="quick-view-orders">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:bg-primary-100">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-sm">عرض الطلبات</span>
                    </a>
                    <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-primary-50 hover:text-primary-600 transition-all group" data-testid="quick-settings">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:bg-primary-100">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-sm">إعدادات المتجر</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
