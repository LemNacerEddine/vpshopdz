@extends('layouts.dashboard')

@section('title', 'المنتجات')

@section('content')
<div class="space-y-6" x-data="productsManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-gray-500">إجمالي <span class="font-bold text-gray-800">{{ $products->total() }}</span> منتج</p>
        </div>
        <button @click="openAddModal()" 
            class="bg-gradient-to-l from-primary-600 to-primary-500 text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all flex items-center gap-2"
            data-testid="add-product-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            إضافة منتج
        </button>
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
                        placeholder="بحث بالاسم أو SKU..."
                        data-testid="search-input">
                </div>
            </div>
            <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" data-testid="status-filter">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
            </select>
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all font-medium" data-testid="search-btn">
                بحث
            </button>
        </form>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-100 transition-all duration-300 group" data-testid="product-card-{{ $product->id }}">
                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                    @if($product->images->first())
                        <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="absolute top-3 right-3 flex flex-col gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold shadow-lg
                            @if($product->status === 'active') bg-green-500 text-white
                            @elseif($product->status === 'draft') bg-yellow-500 text-white
                            @else bg-gray-500 text-white
                            @endif
                        ">
                            @if($product->status === 'active') نشط
                            @elseif($product->status === 'draft') مسودة
                            @else مؤرشف
                            @endif
                        </span>
                    </div>
                    
                    @if($product->discount_percent > 0)
                        <span class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                            -{{ $product->discount_percent }}%
                        </span>
                    @endif

                    <!-- Quick Actions Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                        <div class="flex gap-2">
                            <button @click="editProduct({{ $product->id }})" 
                                class="bg-white text-gray-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-500 hover:text-white transition-colors"
                                data-testid="edit-product-{{ $product->id }}">
                                تعديل
                            </button>
                            <button @click="deleteProduct({{ $product->id }})" 
                                class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-colors"
                                data-testid="delete-product-{{ $product->id }}">
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-gray-800 mb-1 truncate">{{ $product->name_ar ?? $product->name }}</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ $product->category->name_ar ?? 'بدون فئة' }}</p>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xl font-black text-primary-600">{{ number_format($product->price) }}</span>
                            <span class="text-sm text-gray-400">د.ج</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm {{ $product->stock_quantity <= 5 ? 'text-red-500' : 'text-gray-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            {{ $product->stock_quantity }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-gray-100">
                <div class="w-24 h-24 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد منتجات</h3>
                <p class="text-gray-500 mb-6">ابدأ بإضافة منتجاتك الآن لعرضها في متجرك</p>
                <button @click="openAddModal()"
                    class="bg-gradient-to-l from-primary-600 to-primary-500 text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all"
                    data-testid="add-first-product-btn">
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

    <!-- Add/Edit Product Modal -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="showModal = false"
         x-cloak>
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-3xl z-10">
                <h2 class="text-xl font-bold text-gray-800" x-text="editMode ? 'تعديل المنتج' : 'إضافة منتج جديد'"></h2>
                <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-xl transition-colors" data-testid="close-modal-btn">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form method="POST" :action="editMode ? '/dashboard/products/' + productId : '/dashboard/products'" class="p-6 space-y-6">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <!-- Product Names -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        اسم المنتج
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">بالعربية *</label>
                            <input type="text" name="name_ar" x-model="form.name_ar" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="اسم المنتج بالعربية"
                                data-testid="product-name-ar">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">بالفرنسية/الإنجليزية</label>
                            <input type="text" name="name" x-model="form.name"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="Product name"
                                data-testid="product-name-en">
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                        وصف المنتج
                    </h3>
                    <textarea name="description" x-model="form.description" rows="3"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"
                        placeholder="أضف وصفاً تفصيلياً للمنتج..."
                        data-testid="product-description"></textarea>
                </div>

                <!-- Pricing & Stock -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        السعر والمخزون
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">السعر (د.ج) *</label>
                            <input type="number" name="price" x-model="form.price" required min="0" step="0.01"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="0"
                                data-testid="product-price">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">السعر القديم</label>
                            <input type="number" name="compare_price" x-model="form.compare_price" min="0" step="0.01"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="0"
                                data-testid="product-compare-price">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">المخزون *</label>
                            <input type="number" name="stock_quantity" x-model="form.stock_quantity" required min="0"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="0"
                                data-testid="product-stock">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">SKU</label>
                            <input type="text" name="sku" x-model="form.sku"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="ABC-123"
                                data-testid="product-sku">
                        </div>
                    </div>
                </div>

                <!-- Category & Status -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        التصنيف والحالة
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">الفئة</label>
                            <select name="category_id" x-model="form.category_id"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="product-category">
                                <option value="">اختر الفئة</option>
                                @foreach(\App\Models\Category::where('store_id', auth()->user()->store_id)->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name_ar ?? $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">الحالة</label>
                            <select name="status" x-model="form.status"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="product-status">
                                <option value="active">نشط</option>
                                <option value="draft">مسودة</option>
                                <option value="archived">مؤرشف</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        صور المنتج
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">روابط الصور (افصل بينها بفاصلة)</label>
                        <textarea name="images_urls" x-model="form.images_urls" rows="2"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"
                            placeholder="https://example.com/image1.jpg, https://example.com/image2.jpg"
                            data-testid="product-images"></textarea>
                        <p class="text-xs text-gray-400 mt-1">يمكنك إضافة عدة روابط للصور مفصولة بفاصلة</p>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" 
                        class="flex-1 bg-gradient-to-l from-primary-600 to-primary-500 text-white py-4 rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all"
                        data-testid="submit-product-btn">
                        <span x-text="editMode ? 'حفظ التعديلات' : 'إضافة المنتج'"></span>
                    </button>
                    <button type="button" @click="showModal = false"
                        class="px-8 py-4 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all"
                        data-testid="cancel-product-btn">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">حذف المنتج</h3>
            <p class="text-gray-500 mb-6">هل أنت متأكد من حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء.</p>
            <form method="POST" :action="'/dashboard/products/' + deleteProductId" class="flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false"
                    class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all"
                    data-testid="cancel-delete-btn">
                    إلغاء
                </button>
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-all"
                    data-testid="confirm-delete-btn">
                    حذف
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productsManager() {
    return {
        showModal: false,
        showDeleteModal: false,
        editMode: false,
        productId: null,
        deleteProductId: null,
        form: {
            name_ar: '',
            name: '',
            description: '',
            price: '',
            compare_price: '',
            stock_quantity: '',
            sku: '',
            category_id: '',
            status: 'active',
            images_urls: ''
        },
        
        openAddModal() {
            this.editMode = false;
            this.productId = null;
            this.resetForm();
            this.showModal = true;
        },
        
        editProduct(id) {
            this.editMode = true;
            this.productId = id;
            // In real app, fetch product data via AJAX
            // For now, we'll use the form as is
            this.showModal = true;
        },
        
        deleteProduct(id) {
            this.deleteProductId = id;
            this.showDeleteModal = true;
        },
        
        resetForm() {
            this.form = {
                name_ar: '',
                name: '',
                description: '',
                price: '',
                compare_price: '',
                stock_quantity: '',
                sku: '',
                category_id: '',
                status: 'active',
                images_urls: ''
            };
        }
    }
}
</script>
@endpush
@endsection
