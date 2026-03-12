@extends('layouts.dashboard')

@section('title', 'الطلبات')

@section('content')
<div class="space-y-6" x-data="ordersManager()">
    <!-- Toast -->
    <div x-show="toast.show" x-transition
        :class="toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-xl border shadow-lg flex items-center gap-2" x-cloak>
        <span x-text="toast.message"></span>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a href="{{ route('dashboard.orders') }}" class="bg-white rounded-2xl p-4 text-center border {{ !request('status') ? 'ring-2 ring-primary-500 border-primary-200' : 'border-gray-100' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 font-medium">الكل</p>
        </a>
        <a href="{{ route('dashboard.orders', ['status' => 'pending']) }}" class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-4 text-center border {{ request('status') == 'pending' ? 'ring-2 ring-amber-500' : 'border-amber-200' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</p>
            <p class="text-sm text-amber-600 font-medium">قيد الانتظار</p>
        </a>
        <a href="{{ route('dashboard.orders', ['status' => 'confirmed']) }}" class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 text-center border {{ request('status') == 'confirmed' ? 'ring-2 ring-blue-500' : 'border-blue-200' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-blue-600">{{ $stats['confirmed'] }}</p>
            <p class="text-sm text-blue-600 font-medium">مؤكد</p>
        </a>
        <a href="{{ route('dashboard.orders', ['status' => 'shipped']) }}" class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-4 text-center border {{ request('status') == 'shipped' ? 'ring-2 ring-purple-500' : 'border-purple-200' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-purple-600">{{ $stats['shipped'] }}</p>
            <p class="text-sm text-purple-600 font-medium">تم الشحن</p>
        </a>
        <a href="{{ route('dashboard.orders', ['status' => 'delivered']) }}" class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 text-center border {{ request('status') == 'delivered' ? 'ring-2 ring-green-500' : 'border-green-200' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-green-600">{{ $stats['delivered'] }}</p>
            <p class="text-sm text-green-600 font-medium">تم التسليم</p>
        </a>
        <a href="{{ route('dashboard.orders', ['status' => 'cancelled']) }}" class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-4 text-center border {{ request('status') == 'cancelled' ? 'ring-2 ring-red-500' : 'border-red-200' }} hover:shadow-lg transition-all cursor-pointer">
            <p class="text-2xl font-black text-red-600">{{ $stats['cancelled'] }}</p>
            <p class="text-sm text-red-600 font-medium">ملغي</p>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pr-12 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="بحث برقم الطلب أو الهاتف أو الاسم...">
                </div>
            </div>
            <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition-all">
                <option value="">كل الحالات</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد التجهيز</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition-all">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition-all">
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all font-medium">بحث</button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <!-- Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">رقم الطلب</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">العميل</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">المنتجات</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">المجموع</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">الحالة</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">التاريخ</th>
                        <th class="px-5 py-4 text-right text-sm font-bold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    <span class="font-mono font-bold text-gray-800">#{{ $order->order_number }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-gray-800">{{ $order->shipping_name }}</p>
                                <a href="tel:{{ $order->shipping_phone }}" class="text-sm text-primary-600 hover:underline">{{ $order->shipping_phone }}</a>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $order->wilaya->name_ar ?? '' }}{{ $order->commune ? ' - ' . $order->commune->name_ar : '' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-medium">{{ $order->items->count() }} منتج</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-black text-lg text-gray-800">{{ number_format($order->total) }}</span>
                                <span class="text-gray-400 text-sm">د.ج</span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'processing' => 'bg-indigo-100 text-indigo-700',
                                        'shipped' => 'bg-purple-100 text-purple-700',
                                        'delivered' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'returned' => 'bg-gray-100 text-gray-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'قيد الانتظار',
                                        'confirmed' => 'مؤكد',
                                        'processing' => 'قيد التجهيز',
                                        'shipped' => 'تم الشحن',
                                        'delivered' => 'تم التسليم',
                                        'cancelled' => 'ملغي',
                                        'returned' => 'مرتجع',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    @if($order->status === 'pending')<span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>@endif
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-800 font-medium">{{ $order->created_at->format('Y/m/d') }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <!-- View Details -->
                                    <button @click="showOrderDetails({{ $order->id }})" class="p-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-primary-100 hover:text-primary-600 transition-all" title="عرض التفاصيل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <!-- WhatsApp -->
                                    <a href="https://wa.me/213{{ ltrim($order->shipping_phone, '0') }}" target="_blank" class="p-2 bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-all" title="واتساب">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                    <!-- Status change dropdown -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" class="p-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition-all" title="تغيير الحالة">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-20">
                                            @foreach(['pending' => 'قيد الانتظار', 'confirmed' => 'مؤكد', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التسليم', 'cancelled' => 'ملغي'] as $statusVal => $statusLabel)
                                                @if($order->status !== $statusVal)
                                                    <button @click="open = false; updateStatus({{ $order->id }}, '{{ $statusVal }}')"
                                                        class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        @if($statusVal === 'confirmed')<span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                                        @elseif($statusVal === 'processing')<span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                                        @elseif($statusVal === 'shipped')<span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                                        @elseif($statusVal === 'delivered')<span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                                        @elseif($statusVal === 'cancelled')<span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                                        @else<span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                                        @endif
                                                        {{ $statusLabel }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-gray-500 font-bold text-lg mb-1">لا توجد طلبات</p>
                                <p class="text-gray-400 text-sm">ستظهر الطلبات الجديدة هنا عند استلامها</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden divide-y divide-gray-100">
            @forelse($orders as $order)
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-mono font-bold text-gray-800">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 mb-3">
                        <p class="font-bold text-gray-800">{{ $order->shipping_name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->shipping_phone }}</p>
                        <p class="text-xs text-gray-400">{{ $order->wilaya->name_ar ?? '' }}{{ $order->commune ? ' - ' . $order->commune->name_ar : '' }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-black text-xl text-primary-600">{{ number_format($order->total) }}</span>
                            <span class="text-gray-400 text-sm">د.ج</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="showOrderDetails({{ $order->id }})" class="p-2 bg-gray-100 rounded-xl">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            @if($order->status === 'pending')
                                <button @click="updateStatus({{ $order->id }}, 'confirmed')" class="p-2 bg-green-100 rounded-xl">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            @endif
                            <a href="https://wa.me/213{{ ltrim($order->shipping_phone, '0') }}" target="_blank" class="p-2 bg-green-50 rounded-xl">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <p class="text-gray-500">لا توجد طلبات</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="flex justify-center">{{ $orders->links() }}</div>
    @endif

    <!-- Order Details Modal -->
    <div x-show="showDetailsModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showDetailsModal = false" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] overflow-y-auto shadow-2xl">
            <!-- Header -->
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-3xl">
                <h2 class="text-xl font-bold text-gray-800" x-text="order ? 'الطلب #' + order.order_number : 'تفاصيل الطلب'"></h2>
                <button @click="showDetailsModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Loading -->
            <div x-show="detailsLoading" class="p-12 flex justify-center">
                <svg class="w-10 h-10 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>

            <!-- Content -->
            <div x-show="order && !detailsLoading" class="p-6 space-y-5">
                <!-- Status & Date -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500" x-text="order ? new Date(order.created_at).toLocaleDateString('ar-DZ', {year:'numeric',month:'long',day:'numeric'}) : ''"></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select @change="updateStatus(order.id, $event.target.value)"
                            class="px-3 py-1.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 bg-gray-50">
                            <option value="pending" :selected="order && order.status === 'pending'">قيد الانتظار</option>
                            <option value="confirmed" :selected="order && order.status === 'confirmed'">مؤكد</option>
                            <option value="processing" :selected="order && order.status === 'processing'">قيد التجهيز</option>
                            <option value="shipped" :selected="order && order.status === 'shipped'">تم الشحن</option>
                            <option value="delivered" :selected="order && order.status === 'delivered'">تم التسليم</option>
                            <option value="cancelled" :selected="order && order.status === 'cancelled'">ملغي</option>
                        </select>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        معلومات العميل
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">الاسم</p>
                            <p class="font-bold text-gray-800" x-text="order ? order.shipping_name : ''"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">الهاتف</p>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-gray-800" x-text="order ? order.shipping_phone : ''"></p>
                                <template x-if="order">
                                    <a :href="'https://wa.me/213' + (order.shipping_phone || '').replace(/^0/, '')" target="_blank" class="text-green-500 hover:text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-500">الولاية</p>
                            <p class="font-bold text-gray-800" x-text="order ? (order.wilaya_name || '') : ''"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">البلدية</p>
                            <p class="font-bold text-gray-800" x-text="order ? (order.commune_name || '') : ''"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">العنوان التفصيلي</p>
                            <p class="font-bold text-gray-800" x-text="order ? (order.shipping_address || '-') : ''"></p>
                        </div>
                        <template x-if="order && order.notes">
                            <div class="col-span-2">
                                <p class="text-gray-500">ملاحظات العميل</p>
                                <p class="font-bold text-gray-800 bg-yellow-50 p-2 rounded-lg" x-text="order.notes"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        المنتجات
                    </h3>
                    <div class="space-y-3">
                        <template x-for="item in (order ? order.items : [])" :key="item.id">
                            <div class="flex items-center gap-4 bg-gray-50 rounded-xl p-3">
                                <img :src="item.product_image || item.image || '/images/placeholder.png'" class="w-14 h-14 object-cover rounded-xl border border-gray-200 flex-shrink-0" @error="$el.src='/images/placeholder.png'">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 truncate" x-text="item.product_name || item.name"></p>
                                    <p class="text-sm text-gray-500">
                                        <span x-text="Number(item.price).toLocaleString()"></span> د.ج ×
                                        <span x-text="item.quantity"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-gray-800" x-text="Number(item.price * item.quantity).toLocaleString() + ' د.ج'"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="bg-primary-50 rounded-2xl p-4 border border-primary-100">
                    <h3 class="font-bold text-gray-700 mb-3">ملخص الطلب</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">المجموع الفرعي</span>
                            <span class="font-bold" x-text="order ? Number(order.subtotal || order.total).toLocaleString() + ' د.ج' : ''"></span>
                        </div>
                        <template x-if="order && order.discount_amount > 0">
                            <div class="flex justify-between text-red-600">
                                <span>الخصم</span>
                                <span class="font-bold" x-text="'-' + Number(order.discount_amount).toLocaleString() + ' د.ج'"></span>
                            </div>
                        </template>
                        <template x-if="order && order.shipping_cost > 0">
                            <div class="flex justify-between">
                                <span class="text-gray-600">الشحن</span>
                                <span class="font-bold" x-text="Number(order.shipping_cost).toLocaleString() + ' د.ج'"></span>
                            </div>
                        </template>
                        <div class="border-t border-primary-200 pt-2 flex justify-between text-base">
                            <span class="font-bold text-gray-800">المجموع الكلي</span>
                            <span class="font-black text-primary-600 text-xl" x-text="order ? Number(order.total).toLocaleString() + ' د.ج' : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <template x-if="order">
                        <a :href="'https://wa.me/213' + (order.shipping_phone || '').replace(/^0/, '')" target="_blank"
                            class="flex-1 bg-green-500 text-white py-3 rounded-xl font-bold text-center hover:bg-green-600 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            تواصل واتساب
                        </a>
                    </template>
                    <button @click="showDetailsModal = false" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ordersManager() {
    return {
        showDetailsModal: false,
        order: null,
        detailsLoading: false,
        toast: { show: false, message: '', type: 'success' },

        headers() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            };
        },

        showToast(msg, type = 'success') {
            this.toast = { show: true, message: msg, type };
            setTimeout(() => this.toast.show = false, 3000);
        },

        async showOrderDetails(id) {
            this.order = null;
            this.detailsLoading = true;
            this.showDetailsModal = true;
            try {
                const res = await fetch(`/api/v1/dashboard/orders/${id}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();
                this.order = data.data || data;
            } catch (e) {
                this.showDetailsModal = false;
                this.showToast('خطأ في تحميل تفاصيل الطلب', 'error');
            }
            this.detailsLoading = false;
        },

        async updateStatus(id, status) {
            try {
                const res = await fetch(`/api/v1/dashboard/orders/${id}/status`, {
                    method: 'PUT',
                    headers: this.headers(),
                    body: JSON.stringify({ status })
                });
                const data = await res.json();
                if (res.ok) {
                    this.showToast('تم تحديث حالة الطلب بنجاح ✓');
                    if (this.order && this.order.id === id) {
                        this.order.status = status;
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast(data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                this.showToast('خطأ في الاتصال', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
