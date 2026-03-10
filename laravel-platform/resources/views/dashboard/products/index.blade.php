@extends('layouts.dashboard')

@section('title', 'المنتجات')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500">إجمالي {{ $products->total() }} منتج</p>
        </div>
        <button onclick="document.getElementById('addProductModal').classList.remove('hidden')" 
            class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-emerald-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            إضافة منتج
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                    placeholder="بحث بالاسم أو SKU...">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
            </select>
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-200 transition">
                بحث
            </button>
        </form>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="aspect-square bg-gray-100 relative">
                    @if($product->images->first())
                        <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium
                        @if($product->status === 'active') bg-green-100 text-green-700
                        @elseif($product->status === 'draft') bg-yellow-100 text-yellow-700
                        @else bg-gray-100 text-gray-700
                        @endif
                    ">
                        @if($product->status === 'active') نشط
                        @elseif($product->status === 'draft') مسودة
                        @else مؤرشف
                        @endif
                    </span>
                    
                    @if($product->discount_percent > 0)
                        <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            -{{ $product->discount_percent }}%
                        </span>
                    @endif
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-gray-800 mb-1 truncate">{{ $product->name_ar ?? $product->name }}</h3>
                    <p class="text-sm text-gray-500 mb-2">{{ $product->category->name_ar ?? 'بدون فئة' }}</p>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-lg font-bold text-emerald-600">{{ number_format($product->price) }}</span>
                            <span class="text-sm text-gray-400">د.ج</span>
                        </div>
                        <div class="text-sm text-gray-500">
                            المخزون: {{ $product->stock_quantity }}
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-4">
                        <button class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition text-sm">
                            تعديل
                        </button>
                        <button class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد منتجات</h3>
                <p class="text-gray-500 mb-6">ابدأ بإضافة منتجاتك الآن</p>
                <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
                    class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-emerald-700 transition">
                    إضافة منتج جديد
                </button>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($products->hasPages())
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Add Product Modal (Simple version) -->
<div id="addProductModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xl font-bold">إضافة منتج جديد</h2>
            <button onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-500 text-center py-8">
                لإضافة منتجات، استخدم API أو سيتم إضافة نموذج كامل قريباً
            </p>
            <div class="bg-gray-50 rounded-xl p-4 text-sm">
                <p class="font-bold mb-2">API Endpoint:</p>
                <code class="text-emerald-600">POST /api/v1/dashboard/products</code>
            </div>
        </div>
    </div>
</div>
@endsection
