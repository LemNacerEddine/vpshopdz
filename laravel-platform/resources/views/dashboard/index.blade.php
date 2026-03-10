@extends('layouts.dashboard')

@section('title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">إجمالي المنتجات</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['products_count']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">{{ $stats['active_products'] }} نشط</p>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">إجمالي الطلبات</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['orders_count']) }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-orange-600 mt-2">{{ $stats['pending_orders'] }} بانتظار التأكيد</p>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">طلبات اليوم</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['today_orders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ number_format($stats['today_revenue']) }} د.ج</p>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">إجمالي الإيرادات</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_revenue']) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">د.ج</p>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('dashboard.products') }}" class="bg-gradient-to-l from-blue-500 to-blue-600 text-white rounded-2xl p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-lg">إضافة منتج</p>
                    <p class="text-white/80 text-sm">أضف منتج جديد لمتجرك</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('dashboard.orders') }}" class="bg-gradient-to-l from-emerald-500 to-emerald-600 text-white rounded-2xl p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-lg">إدارة الطلبات</p>
                    <p class="text-white/80 text-sm">تتبع وإدارة طلباتك</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('dashboard.settings') }}" class="bg-gradient-to-l from-purple-500 to-purple-600 text-white rounded-2xl p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-lg">إعدادات المتجر</p>
                    <p class="text-white/80 text-sm">تخصيص متجرك</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-lg text-gray-800">آخر الطلبات</h2>
                    <a href="{{ route('dashboard.orders') }}" class="text-emerald-600 text-sm hover:underline">عرض الكل</a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentOrders as $order)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $order->order_number }}</p>
                                <p class="text-sm text-gray-500">{{ $order->shipping_name }}</p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">{{ number_format($order->total) }} د.ج</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-700
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
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p>لا توجد طلبات بعد</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <h2 class="font-bold text-lg text-gray-800">تنبيه المخزون المنخفض</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($lowStock as $product)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $product->name_ar ?? $product->name }}</p>
                                <p class="text-sm text-gray-500">SKU: {{ $product->sku ?? 'N/A' }}</p>
                            </div>
                            <div class="text-left">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700">
                                    {{ $product->stock_quantity }} متبقي
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p>جميع المنتجات متوفرة بكميات كافية</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Store Info Card -->
    <div class="bg-gradient-to-l from-emerald-600 to-teal-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold mb-2">رابط متجرك</h3>
                <p class="opacity-90">شارك هذا الرابط مع عملائك</p>
                <div class="mt-4 bg-white/20 rounded-lg px-4 py-2 inline-block">
                    <code class="text-sm">{{ url('/store/' . $store->slug) }}</code>
                </div>
            </div>
            <a href="{{ url('/store/' . $store->slug) }}" target="_blank" class="bg-white text-emerald-600 px-6 py-3 rounded-xl font-bold hover:bg-gray-100 transition">
                زيارة المتجر
            </a>
        </div>
    </div>
</div>
@endsection
