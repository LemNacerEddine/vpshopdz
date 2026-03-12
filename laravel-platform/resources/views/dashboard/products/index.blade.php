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
            class="bg-gradient-to-l from-primary-600 to-primary-500 text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة منتج
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pr-12 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="بحث بالاسم أو SKU...">
                </div>
            </div>
            <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
            </select>
            <select name="category" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                <option value="">كل الفئات</option>
                @foreach(\App\Models\Category::where('store_id', auth()->user()->store_id)->get() as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name_ar ?? $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all font-medium">بحث</button>
        </form>
    </div>

    <!-- Toast notification -->
    <div x-show="toast.show" x-transition
        :class="toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-xl border shadow-lg flex items-center gap-2" x-cloak>
        <span x-text="toast.message"></span>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-100 transition-all duration-300 group">
                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                    @if($product->images->first())
                        <img src="{{ $product->images->first()->url }}" alt="{{ $product->name_ar ?? $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold shadow {{ $product->status === 'active' ? 'bg-green-500 text-white' : ($product->status === 'draft' ? 'bg-yellow-500 text-white' : 'bg-gray-500 text-white') }}">
                            {{ $product->status === 'active' ? 'نشط' : ($product->status === 'draft' ? 'مسودة' : 'مؤرشف') }}
                        </span>
                    </div>
                    @if($product->discount_percent > 0)
                        <span class="absolute top-3 left-3 bg-red-500 text-white px-2.5 py-1 rounded-full text-xs font-bold shadow">-{{ $product->discount_percent }}%</span>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                        <div class="flex gap-2">
                            <button @click="editProduct({{ $product->id }})" class="bg-white text-gray-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-500 hover:text-white transition-colors">تعديل</button>
                            <button @click="confirmDelete({{ $product->id }}, '{{ addslashes($product->name_ar ?? $product->name) }}')" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-colors">حذف</button>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-gray-800 mb-1 truncate">{{ $product->name_ar ?? $product->name }}</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ $product->category->name_ar ?? 'بدون فئة' }}</p>
                    <div class="flex items-center justify-between">
                        <div>
                            @if($product->compare_at_price > $product->price)
                                <span class="text-sm text-gray-400 line-through ml-1">{{ number_format($product->compare_at_price) }}</span>
                            @endif
                            <span class="text-xl font-black text-primary-600">{{ number_format($product->price) }}</span>
                            <span class="text-sm text-gray-400">د.ج</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm {{ $product->stock_quantity <= 5 ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            {{ $product->stock_quantity }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-gray-100">
                <div class="w-24 h-24 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد منتجات</h3>
                <p class="text-gray-500 mb-6">ابدأ بإضافة منتجاتك الآن</p>
                <button @click="openAddModal()" class="bg-gradient-to-l from-primary-600 to-primary-500 text-white px-8 py-3 rounded-xl font-bold">إضافة منتج جديد</button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="flex justify-center">{{ $products->links() }}</div>
    @endif

    <!-- Add/Edit Product Modal -->
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false" x-cloak>
        <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[92vh] overflow-y-auto shadow-2xl">
            <!-- Header -->
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-3xl z-10">
                <h2 class="text-xl font-bold text-gray-800" x-text="editMode ? 'تعديل المنتج' : 'إضافة منتج جديد'"></h2>
                <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Loading -->
            <div x-show="formLoading" class="p-12 flex items-center justify-center">
                <svg class="w-10 h-10 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6" x-show="!formLoading">
                <!-- 1. اسم المنتج -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs font-black">1</span>
                        اسم المنتج
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">بالعربية *</label>
                            <input type="text" x-model="form.name_ar" placeholder="اسم المنتج بالعربية"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">بالفرنسية / الإنجليزية</label>
                            <input type="text" x-model="form.name" placeholder="Nom du produit"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                    </div>
                </div>

                <!-- 2. الوصف -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs font-black">2</span>
                        الوصف
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">بالعربية</label>
                            <textarea x-model="form.description_ar" rows="3" placeholder="وصف المنتج بالعربية..."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">بالفرنسية / الإنجليزية</label>
                            <textarea x-model="form.description" rows="3" placeholder="Description du produit..."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- 3. السعر والمخزون -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs font-black">3</span>
                        السعر والمخزون
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">السعر (د.ج) *</label>
                            <input type="number" x-model="form.price" min="0" step="1" placeholder="0"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">السعر الأصلي</label>
                            <input type="number" x-model="form.compare_at_price" min="0" step="1" placeholder="0"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">المخزون *</label>
                            <input type="number" x-model="form.stock_quantity" min="0" placeholder="0"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">SKU</label>
                            <input type="text" x-model="form.sku" placeholder="ABC-001"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                        </div>
                    </div>
                </div>

                <!-- 4. التصنيف والحالة -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs font-black">4</span>
                        التصنيف والحالة
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">الفئة</label>
                            <select x-model="form.category_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                                <option value="">بدون فئة</option>
                                @foreach(\App\Models\Category::where('store_id', auth()->user()->store_id)->get() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name_ar ?? $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">الحالة</label>
                            <select x-model="form.status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                                <option value="active">نشط</option>
                                <option value="draft">مسودة</option>
                                <option value="archived">مؤرشف</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-2.5">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" x-model="form.is_featured" class="w-5 h-5 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                                <span class="text-sm font-medium text-gray-700">منتج مميز ⭐</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 5. التخفيض -->
                <div class="bg-orange-50 rounded-2xl p-4 border border-orange-100">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2">
                            <span class="w-6 h-6 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-xs font-black">5</span>
                            التخفيض
                        </h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="showDiscount" class="w-4 h-4 text-orange-500 rounded border-gray-300">
                            <span class="text-sm text-gray-600">تفعيل التخفيض</span>
                        </label>
                    </div>
                    <div x-show="showDiscount" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">النسبة (%)</label>
                            <input type="number" x-model="form.discount_percent" min="1" max="99" placeholder="0"
                                class="w-full px-4 py-2.5 bg-white border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">تاريخ البداية</label>
                            <input type="date" x-model="form.discount_start"
                                class="w-full px-4 py-2.5 bg-white border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">تاريخ الانتهاء</label>
                            <input type="date" x-model="form.discount_end"
                                class="w-full px-4 py-2.5 bg-white border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
                        </div>
                    </div>
                    <template x-if="showDiscount && form.price > 0 && form.discount_percent > 0">
                        <div class="mt-3 p-3 bg-white rounded-xl border border-orange-200">
                            <p class="text-sm text-gray-600">
                                السعر بعد التخفيض:
                                <span class="font-black text-orange-600 text-lg" x-text="Math.round(form.price * (1 - form.discount_percent / 100)).toLocaleString() + ' د.ج'"></span>
                                <span class="text-gray-400 line-through mr-2" x-text="Number(form.price).toLocaleString() + ' د.ج'"></span>
                            </p>
                        </div>
                    </template>
                </div>

                <!-- 6. الصور -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs font-black">6</span>
                        صور المنتج
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">روابط الصور (كل رابط في سطر)</label>
                            <textarea x-model="imagesText" rows="3" @input="parseImages()"
                                placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none font-mono text-sm"></textarea>
                        </div>
                        <div x-show="form.images.length > 0" class="flex flex-wrap gap-3">
                            <template x-for="(img, i) in form.images" :key="i">
                                <div class="relative group">
                                    <img :src="img" class="w-20 h-20 object-cover rounded-xl border border-gray-200" @@error="$el.src='/images/placeholder.png'">
                                    <span x-show="i === 0" class="absolute -top-1.5 -right-1.5 bg-primary-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">رئيسية</span>
                                    <button type="button" @click="form.images.splice(i, 1); updateImagesText()"
                                        class="absolute -top-1.5 -left-1.5 hidden group-hover:flex w-5 h-5 bg-red-500 text-white rounded-full items-center justify-center text-xs font-bold">×</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Error -->
                <template x-if="errors">
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <p class="text-red-600 text-sm" x-text="errors"></p>
                    </div>
                </template>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="saveProduct()" :disabled="saving"
                        class="flex-1 bg-gradient-to-l from-primary-600 to-primary-500 text-white py-3.5 rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all disabled:opacity-60 flex items-center justify-center gap-2">
                        <svg x-show="saving" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving ? 'جاري الحفظ...' : (editMode ? 'حفظ التعديلات' : 'إضافة المنتج')"></span>
                    </button>
                    <button type="button" @click="showModal = false" class="px-8 py-3.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">حذف المنتج</h3>
            <p class="text-gray-500 mb-1">هل أنت متأكد من حذف:</p>
            <p class="font-bold text-gray-700 mb-6" x-text='"«" + deleteProductName + "»"'></p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200">إلغاء</button>
                <button @click="doDelete()" :disabled="deleting" class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 disabled:opacity-60">
                    <span x-text="deleting ? 'جاري الحذف...' : 'تأكيد الحذف'"></span>
                </button>
            </div>
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
        deleteProductName: '',
        saving: false,
        deleting: false,
        formLoading: false,
        showDiscount: false,
        imagesText: '',
        errors: null,
        toast: { show: false, message: '', type: 'success' },
        form: {
            name_ar: '', name: '', description_ar: '', description: '',
            price: '', compare_at_price: '', stock_quantity: 0, sku: '',
            category_id: '', status: 'active', is_featured: false,
            discount_percent: 0, discount_start: '', discount_end: '',
            images: []
        },

        headers() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            };
        },

        showToast(msg, type = 'success') {
            this.toast = { show: true, message: msg, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        parseImages() {
            this.form.images = this.imagesText.split('\n').map(s => s.trim()).filter(Boolean);
        },

        updateImagesText() {
            this.imagesText = this.form.images.join('\n');
        },

        resetForm() {
            this.form = {
                name_ar: '', name: '', description_ar: '', description: '',
                price: '', compare_at_price: '', stock_quantity: 0, sku: '',
                category_id: '', status: 'active', is_featured: false,
                discount_percent: 0, discount_start: '', discount_end: '',
                images: []
            };
            this.imagesText = '';
            this.showDiscount = false;
            this.errors = null;
        },

        openAddModal() {
            this.editMode = false;
            this.productId = null;
            this.resetForm();
            this.showModal = true;
        },

        async editProduct(id) {
            this.editMode = true;
            this.productId = id;
            this.formLoading = true;
            this.errors = null;
            this.showModal = true;
            try {
                const res = await fetch(`/api/v1/dashboard/products/${id}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();
                const p = data.data || data;
                this.form = {
                    name_ar: p.name_ar || '',
                    name: p.name || '',
                    description_ar: p.description_ar || '',
                    description: p.description || '',
                    price: p.price || '',
                    compare_at_price: p.compare_at_price || '',
                    stock_quantity: p.stock_quantity ?? 0,
                    sku: p.sku || '',
                    category_id: p.category_id || '',
                    status: p.status || 'active',
                    is_featured: p.is_featured || false,
                    discount_percent: p.discount_percent || 0,
                    discount_start: p.discount_start ? p.discount_start.substring(0, 10) : '',
                    discount_end: p.discount_end ? p.discount_end.substring(0, 10) : '',
                    images: Array.isArray(p.images) ? p.images.map(img => img.url || img) : []
                };
                this.imagesText = this.form.images.join('\n');
                this.showDiscount = this.form.discount_percent > 0;
            } catch (e) {
                this.errors = 'خطأ في تحميل بيانات المنتج';
            }
            this.formLoading = false;
        },

        async saveProduct() {
            if (!this.form.name_ar && !this.form.name) {
                this.errors = 'اسم المنتج مطلوب (بالعربية أو الفرنسية)';
                return;
            }
            if (!this.form.price) {
                this.errors = 'السعر مطلوب';
                return;
            }
            this.saving = true;
            this.errors = null;

            const payload = {
                ...this.form,
                name: this.form.name || this.form.name_ar,
                discount_percent: this.showDiscount ? (this.form.discount_percent || 0) : 0,
                discount_start: this.showDiscount ? this.form.discount_start : null,
                discount_end: this.showDiscount ? this.form.discount_end : null,
            };

            try {
                const url = this.editMode
                    ? `/api/v1/dashboard/products/${this.productId}`
                    : '/api/v1/dashboard/products';
                const res = await fetch(url, {
                    method: this.editMode ? 'PUT' : 'POST',
                    headers: this.headers(),
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok && data.success !== false) {
                    this.showModal = false;
                    this.showToast(this.editMode ? 'تم تحديث المنتج بنجاح ✓' : 'تم إضافة المنتج بنجاح ✓');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.errors = data.message || (data.errors ? Object.values(data.errors).flat().join(' | ') : 'حدث خطأ');
                }
            } catch (e) {
                this.errors = 'خطأ في الاتصال بالخادم';
            }
            this.saving = false;
        },

        confirmDelete(id, name) {
            this.deleteProductId = id;
            this.deleteProductName = name;
            this.showDeleteModal = true;
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res = await fetch(`/api/v1/dashboard/products/${this.deleteProductId}`, {
                    method: 'DELETE',
                    headers: this.headers()
                });
                if (res.ok) {
                    this.showDeleteModal = false;
                    this.showToast('تم حذف المنتج بنجاح');
                    setTimeout(() => window.location.reload(), 800);
                }
            } catch (e) {}
            this.deleting = false;
        }
    }
}
</script>
@endpush
@endsection
