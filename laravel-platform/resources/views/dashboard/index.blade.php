@extends('layouts.dashboard')

@section('title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-l from-primary-600 via-primary-500 to-emerald-400 rounded-2xl p-6 lg:p-8 text-white">
        <div class="relative z-10">
            <h2 class="text-2xl lg:text-3xl font-bold mb-2">مرحباً، {{ auth()->user()->name ?? 'التاجر' }}! 👋</h2>
            <p class="text-white/80 text-lg">إليك ملخص أداء متجرك اليوم</p>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/10 rounded-full translate-x-1/4 translate-y-1/4"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg font-medium">+12%</span>
            </div>
            <p class="text-sm text-gray-500 mb-1">إجمالي الإيرادات</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_revenue']) }} <span class="text-sm font-normal text-gray-400">د.ج</span></p>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-lg font-medium">{{ $stats['today_orders'] }} اليوم</span>
            </div>
            <p class="text-sm text-gray-500 mb-1">إجمالي الطلبات</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['orders_count']) }}</p>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded-lg font-medium">{{ $stats['active_products'] }} نشط</span>
            </div>
            <p class="text-sm text-gray-500 mb-1">المنتجات</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['products_count']) }}</p>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                @if($stats['pending_orders'] > 0)
                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-lg font-medium animate-pulse">تحتاج انتباه</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mb-1">طلبات معلقة</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['pending_orders']) }}</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('dashboard.products') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all">
            <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary-200 transition-colors">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <p class="font-medium text-gray-800 group-hover:text-primary-600 transition-colors">إضافة منتج</p>
            <p class="text-xs text-gray-400 mt-1">منتج جديد لمتجرك</p>
        </a>
        
        <a href="{{ route('dashboard.orders') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 hover:border-blue-200 hover:shadow-lg transition-all">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <p class="font-medium text-gray-800 group-hover:text-blue-600 transition-colors">إدارة الطلبات</p>
            <p class="text-xs text-gray-400 mt-1">تتبع وإدارة طلباتك</p>
        </a>
        
        <a href="{{ route('dashboard.settings') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 hover:border-purple-200 hover:shadow-lg transition-all">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <p class="font-medium text-gray-800 group-hover:text-purple-600 transition-colors">إعدادات المتجر</p>
            <p class="text-xs text-gray-400 mt-1">تخصيص متجرك</p>
        </a>
        
        <a href="/store/{{ $store->slug ?? '' }}" target="_blank" class="group bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl p-5 hover:shadow-lg hover:shadow-primary-500/30 transition-all">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </div>
            <p class="font-medium text-white">زيارة المتجر</p>
            <p class="text-xs text-white/70 mt-1">عرض متجرك للعملاء</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-800">آخر الطلبات</h3>
                    <p class="text-sm text-gray-500">آخر 5 طلبات وردت لمتجرك</p>
                </div>
                <a href="{{ route('dashboard.orders') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">عرض الكل</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                    <div class="p-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->shipping_name }} • {{ $order->items->count() }} منتج</p>
                                </div>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">{{ number_format($order->total) }} د.ج</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($order->status === 'pending') bg-amber-100 text-amber-700
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700
                                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">
                                    @switch($order->status)
                                        @case('pending') قيد الانتظار @break
                                        @case('confirmed') مؤكد @break
                                        @case('shipped') تم الشحن @break
                                        @case('delivered') تم التسليم @break
                                        @case('cancelled') ملغي @break
                                        @default {{ $order->status }}
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">لا توجد طلبات بعد</p>
                        <p class="text-sm text-gray-400 mt-1">ستظهر الطلبات الجديدة هنا</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">تنبيه المخزون</h3>
                <p class="text-sm text-gray-500">منتجات تحتاج إعادة تعبئة</p>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStock as $product)
                    <div class="p-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                @if($product->images->first())
                                    <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ $product->name_ar ?? $product->name }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    {{ $product->stock_quantity }} متبقي
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">المخزون جيد</p>
                        <p class="text-xs text-gray-400 mt-1">جميع المنتجات متوفرة</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Store Link Card -->
    <div class="bg-gradient-to-l from-gray-900 via-gray-800 to-gray-900 rounded-2xl p-6 lg:p-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="text-center lg:text-right">
                <h3 class="text-xl lg:text-2xl font-bold text-white mb-2">رابط متجرك</h3>
                <p class="text-gray-400">شارك هذا الرابط مع عملائك</p>
                <div class="mt-4 bg-white/10 rounded-xl px-4 py-3 inline-flex items-center gap-3">
                    <code class="text-primary-400 text-sm lg:text-base">{{ url('/store/' . ($store->slug ?? 'my-store')) }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/store/' . ($store->slug ?? 'my-store')) }}')" class="p-2 hover:bg-white/10 rounded-lg transition-colors" title="نسخ الرابط">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <a href="/store/{{ $store->slug ?? '' }}" target="_blank" class="bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-xl font-bold transition-colors shadow-lg shadow-primary-500/30">
                فتح المتجر
            </a>
        </div>
    </div>
</div>
@endsection
