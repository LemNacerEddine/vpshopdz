@extends('layouts.dashboard')

@section('title', 'الطلبات')

@section('content')
<div class="space-y-6" x-data="ordersManager()">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-4 text-center border border-gray-100 hover:shadow-lg transition-all cursor-pointer {{ !request('status') ? 'ring-2 ring-primary-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders') }}'"
            data-testid="filter-all">
            <p class="text-2xl font-black text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 font-medium">الكل</p>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-4 text-center border border-amber-200 hover:shadow-lg transition-all cursor-pointer {{ request('status') == 'pending' ? 'ring-2 ring-amber-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders', ['status' => 'pending']) }}'"
            data-testid="filter-pending">
            <p class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</p>
            <p class="text-sm text-amber-600 font-medium">قيد الانتظار</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 text-center border border-blue-200 hover:shadow-lg transition-all cursor-pointer {{ request('status') == 'confirmed' ? 'ring-2 ring-blue-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders', ['status' => 'confirmed']) }}'"
            data-testid="filter-confirmed">
            <p class="text-2xl font-black text-blue-600">{{ $stats['confirmed'] }}</p>
            <p class="text-sm text-blue-600 font-medium">مؤكد</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-4 text-center border border-purple-200 hover:shadow-lg transition-all cursor-pointer {{ request('status') == 'shipped' ? 'ring-2 ring-purple-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders', ['status' => 'shipped']) }}'"
            data-testid="filter-shipped">
            <p class="text-2xl font-black text-purple-600">{{ $stats['shipped'] }}</p>
            <p class="text-sm text-purple-600 font-medium">تم الشحن</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 text-center border border-green-200 hover:shadow-lg transition-all cursor-pointer {{ request('status') == 'delivered' ? 'ring-2 ring-green-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders', ['status' => 'delivered']) }}'"
            data-testid="filter-delivered">
            <p class="text-2xl font-black text-green-600">{{ $stats['delivered'] }}</p>
            <p class="text-sm text-green-600 font-medium">تم التسليم</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-4 text-center border border-red-200 hover:shadow-lg transition-all cursor-pointer {{ request('status') == 'cancelled' ? 'ring-2 ring-red-500' : '' }}"
            onclick="window.location.href='{{ route('dashboard.orders', ['status' => 'cancelled']) }}'"
            data-testid="filter-cancelled">
            <p class="text-2xl font-black text-red-600">{{ $stats['cancelled'] }}</p>
            <p class="text-sm text-red-600 font-medium">ملغي</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        class="w-full pr-12 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="بحث برقم الطلب أو الهاتف..."
                        data-testid="search-input">
                </div>
            </div>
            <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" data-testid="status-select">
                <option value="">كل الحالات</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                data-testid="date-from">
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                data-testid="date-to">
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all font-medium" data-testid="search-btn">
                بحث
            </button>
        </form>
    </div>
    
    <!-- Orders List -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
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
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors" data-testid="order-row-{{ $order->order_number }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <span class="font-mono font-bold text-gray-800">#{{ $order->order_number }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-gray-800">{{ $order->shipping_name }}</p>
                                    <p class="text-sm text-gray-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ $order->shipping_phone }}
                                    </p>
                                    <p class="text-xs text-gray-400 flex items-center gap-1 mt-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $order->wilaya->name_ar ?? '' }} - {{ $order->commune->name_ar ?? '' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-medium">
                                    {{ $order->items->count() }} منتج
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <span class="font-black text-xl text-gray-800">{{ number_format($order->total) }}</span>
                                    <span class="text-gray-400 text-sm">د.ج</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                                    @if($order->status === 'pending') bg-amber-100 text-amber-700
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700
                                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">
                                    @switch($order->status)
                                        @case('pending') 
                                            <span class="w-2 h-2 bg-amber-500 rounded-full ml-2 animate-pulse"></span>
                                            قيد الانتظار 
                                            @break
                                        @case('confirmed') 
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            مؤكد 
                                            @break
                                        @case('processing') قيد التجهيز @break
                                        @case('shipped') 
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                            تم الشحن 
                                            @break
                                        @case('delivered') 
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            تم التسليم 
                                            @break
                                        @case('cancelled') 
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            ملغي 
                                            @break
                                        @case('returned') مرتجع @break
                                        @default {{ $order->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <p class="text-gray-800 font-medium">{{ $order->created_at->format('Y/m/d') }}</p>
                                    <p class="text-gray-400 text-xs">{{ $order->created_at->format('H:i') }}</p>
                                    <p class="text-gray-400 text-xs mt-1">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="showOrderDetails({{ $order->id }})" 
                                        class="p-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-primary-100 hover:text-primary-600 transition-all" 
                                        title="عرض التفاصيل"
                                        data-testid="view-order-{{ $order->order_number }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if($order->status === 'pending')
                                        <form method="POST" action="{{ route('dashboard.orders.update', $order) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="p-2.5 bg-green-100 text-green-600 rounded-xl hover:bg-green-200 transition-all" title="تأكيد" data-testid="confirm-order-{{ $order->order_number }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($order->status, ['pending', 'confirmed']))
                                        <form method="POST" action="{{ route('dashboard.orders.update', $order) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="p-2.5 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-all" title="إلغاء" data-testid="cancel-order-{{ $order->order_number }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
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
                <div class="p-4 hover:bg-gray-50/50 transition-colors" data-testid="order-card-{{ $order->order_number }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-mono font-bold text-gray-800">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            @if($order->status === 'pending') bg-amber-100 text-amber-700
                            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                            @elseif($order->status === 'delivered') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700
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
                    
                    <div class="bg-gray-50 rounded-xl p-3 mb-3">
                        <p class="font-bold text-gray-800">{{ $order->shipping_name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->shipping_phone }}</p>
                        <p class="text-xs text-gray-400">{{ $order->wilaya->name_ar ?? '' }} - {{ $order->commune->name_ar ?? '' }}</p>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-black text-xl text-primary-600">{{ number_format($order->total) }}</span>
                            <span class="text-gray-400 text-sm">د.ج</span>
                        </div>
                        <div class="flex gap-2">
                            <button class="p-2 bg-gray-100 rounded-lg" data-testid="view-order-mobile-{{ $order->order_number }}">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            @if($order->status === 'pending')
                                <button class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500">لا توجد طلبات</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="flex justify-center">
            {{ $orders->links() }}
        </div>
    @endif

    <!-- Order Details Modal -->
    <div x-show="showDetailsModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="showDetailsModal = false"
         x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-3xl">
                <h2 class="text-xl font-bold text-gray-800">تفاصيل الطلب</h2>
                <button @click="showDetailsModal = false" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-500 text-center py-8">قم بالنقر على زر العرض لرؤية تفاصيل الطلب</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ordersManager() {
    return {
        showDetailsModal: false,
        selectedOrder: null,
        
        showOrderDetails(orderId) {
            this.selectedOrder = orderId;
            this.showDetailsModal = true;
        }
    }
}
</script>
@endpush
@endsection
