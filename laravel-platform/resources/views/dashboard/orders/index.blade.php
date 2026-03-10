@extends('layouts.dashboard')

@section('title', 'الطلبات')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500">الكل</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-200">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-sm text-yellow-600">قيد الانتظار</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-200">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['confirmed'] }}</p>
            <p class="text-sm text-blue-600">مؤكد</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-200">
            <p class="text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</p>
            <p class="text-sm text-purple-600">تم الشحن</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-200">
            <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] }}</p>
            <p class="text-sm text-green-600">تم التسليم</p>
        </div>
        <div class="bg-red-50 rounded-xl p-4 text-center border border-red-200">
            <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
            <p class="text-sm text-red-600">ملغي</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                    placeholder="بحث برقم الطلب أو الهاتف...">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                <option value="">كل الحالات</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-200 transition">
                بحث
            </button>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">رقم الطلب</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">العميل</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">المنتجات</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">المجموع</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">الحالة</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-gray-800">{{ $order->order_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $order->shipping_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->shipping_phone }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-sm">
                                    {{ $order->items->count() }} منتج
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800">{{ number_format($order->total) }}</span>
                                <span class="text-gray-400 text-sm">د.ج</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700
                                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">
                                    @switch($order->status)
                                        @case('pending') قيد الانتظار @break
                                        @case('confirmed') مؤكد @break
                                        @case('processing') قيد التجهيز @break
                                        @case('shipped') تم الشحن @break
                                        @case('delivered') تم التسليم @break
                                        @case('cancelled') ملغي @break
                                        @case('returned') مرتجع @break
                                        @default {{ $order->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $order->created_at->format('Y/m/d') }}
                                <br>
                                <span class="text-xs">{{ $order->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="showOrderDetails('{{ $order->id }}')" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition" title="عرض التفاصيل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if($order->status === 'pending')
                                        <button class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition" title="تأكيد">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-gray-500">لا توجد طلبات</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="flex justify-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
