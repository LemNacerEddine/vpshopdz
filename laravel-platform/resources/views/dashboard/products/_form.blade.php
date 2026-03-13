@php
$isEdit = !is_null($product);
$productJson = $isEdit ? json_encode([
    'name_ar'           => $product->name_ar ?? '',
    'name_fr'           => $product->name_fr ?? '',
    'name'              => $product->name ?? '',
    'description_ar'    => $product->description_ar ?? '',
    'description_fr'    => $product->description_fr ?? '',
    'description'       => $product->description ?? '',
    'price'             => $product->price ?? '',
    'compare_at_price'  => $product->compare_at_price ?? '',
    'cost_price'        => $product->cost_price ?? '',
    'sku'               => $product->sku ?? '',
    'stock_quantity'    => $product->stock_quantity ?? 0,
    'track_inventory'   => $product->track_inventory ?? true,
    'has_variants'      => $product->has_variants ?? false,
    'category_id'       => $product->category_id ?? '',
    'status'            => $product->status ?? 'active',
    'is_featured'       => $product->is_featured ?? false,
    'discount_percent'  => $product->discount_percent ?? 0,
    'weight'            => $product->weight ?? '',
    'low_stock_threshold' => $product->low_stock_threshold ?? 5,
    'shipping_type'     => $product->shipping_type ?? 'standard',
]) : 'null';

$imagesJson = $isEdit ? json_encode($product->images->map(fn($img) => ['url' => $img->url, 'type' => 'image'])->values()) : '[]';

$optionsJson = $isEdit && $product->has_variants ? json_encode($product->options->map(fn($opt) => [
    'name' => $opt->name,
    'values' => $opt->values->pluck('value')->values(),
])->values()) : '[]';

$variantsJson = $isEdit && $product->has_variants ? json_encode($product->variants->map(fn($v) => [
    'id' => $v->id,
    'name' => $v->name,
    'sku' => $v->sku ?? '',
    'price' => $v->price ?? '',
    'stock_quantity' => $v->stock_quantity,
    'options' => $v->options ?? [],
    'is_active' => $v->is_active,
])->values()) : '[]';
@endphp

<div x-data="productForm()" x-init="init()" class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.products') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-xl font-bold text-gray-800">{{ $pageTitle }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="form.status = 'draft'; save()"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors" :disabled="saving">
                    حفظ كمسودة
                </button>
                <button type="button" @click="form.status = 'active'; save()"
                    class="px-6 py-2 rounded-xl bg-primary-600 text-white font-bold hover:bg-primary-700 transition-colors flex items-center gap-2" :disabled="saving">
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="saving ? 'جاري الحفظ...' : 'حفظ ونشر'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition
        :class="toast.type === 'success' ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-800'"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-xl border shadow-lg flex items-center gap-2 min-w-[280px] justify-center" x-cloak>
        <span x-text="toast.message"></span>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Column --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 1. اسم المنتج --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">1</span>
                        معلومات المنتج
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم بالعربية <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.name_ar" placeholder="اسم المنتج بالعربية"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم بالفرنسية</label>
                                <input type="text" x-model="form.name_fr" placeholder="Nom en français"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم بالإنجليزية</label>
                                <input type="text" x-model="form.name" placeholder="Product name in English"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. الوصف --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">2</span>
                        الوصف
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">الوصف بالعربية</label>
                            <textarea x-model="form.description_ar" rows="4" placeholder="وصف المنتج بالعربية..."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none resize-none transition-all"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">الوصف بالفرنسية</label>
                                <textarea x-model="form.description_fr" rows="3" placeholder="Description en français..."
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none resize-none transition-all"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">الوصف بالإنجليزية</label>
                                <textarea x-model="form.description" rows="3" placeholder="Product description in English..."
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none resize-none transition-all"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. الوسائط (الصور) --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">3</span>
                        الصور والوسائط
                    </h2>

                    {{-- Images grid --}}
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-4" x-show="images.length > 0">
                        <template x-for="(img, i) in images" :key="i">
                            <div class="relative group aspect-square rounded-xl overflow-hidden border-2"
                                :class="i === 0 ? 'border-primary-400' : 'border-gray-200'">
                                <img :src="img.url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
                                    <button type="button" @click="removeImage(i)" class="p-1.5 bg-red-500 rounded-lg text-white">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <button type="button" x-show="i > 0" @click="setPrimary(i)" class="p-1.5 bg-primary-500 rounded-lg text-white text-xs font-bold">رئيسية</button>
                                </div>
                                <span x-show="i === 0" class="absolute bottom-1 right-1 bg-primary-500 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold">رئيسية</span>
                            </div>
                        </template>

                        {{-- Add image button --}}
                        <div class="aspect-square border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                            @click="$refs.fileInput.click()">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                    </div>

                    {{-- Upload area (shown when no images) --}}
                    <div x-show="images.length === 0"
                        class="border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent @drop.prevent="handleDrop($event)">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-gray-600 font-medium mb-1">اسحب الصور هنا أو انقر للرفع</p>
                        <p class="text-gray-400 text-sm">PNG, JPG, WebP — حتى 10 ميجابايت</p>
                    </div>

                    <input type="file" x-ref="fileInput" accept="image/*,video/mp4" multiple class="hidden" @change="handleFileSelect($event)">

                    {{-- URL input --}}
                    <div class="mt-4 flex gap-2">
                        <input type="url" x-model="newImageUrl" placeholder="أو أضف رابط صورة / فيديو..."
                            class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none text-sm transition-all"
                            @keydown.enter.prevent="addImageFromUrl()">
                        <button type="button" @click="addImageFromUrl()"
                            class="px-4 py-2.5 bg-gray-100 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium text-sm whitespace-nowrap">
                            إضافة
                        </button>
                    </div>
                    <p x-show="uploadingCount > 0" class="text-xs text-primary-600 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        جاري رفع <span x-text="uploadingCount"></span> ملف...
                    </p>
                </div>

                {{-- 4. التسعير --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">4</span>
                        التسعير
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">سعر البيع <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" x-model="form.price" min="0" step="1" placeholder="0"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none pr-12 transition-all">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">د.ج</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">السعر الأصلي</label>
                            <div class="relative">
                                <input type="number" x-model="form.compare_at_price" min="0" step="1" placeholder="0"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none pr-12 transition-all">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">د.ج</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">سعر التكلفة</label>
                            <div class="relative">
                                <input type="number" x-model="form.cost_price" min="0" step="1" placeholder="0"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none pr-12 transition-all">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">د.ج</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">نسبة الخصم (%)</label>
                            <input type="number" x-model="form.discount_percent" min="0" max="100" step="1" placeholder="0"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                    <div x-show="form.price && form.compare_at_price && form.compare_at_price > form.price" class="mt-3 p-3 bg-green-50 rounded-xl text-sm text-green-700">
                        💰 توفير <strong x-text="Math.round((1 - form.price / form.compare_at_price) * 100) + '%'"></strong>
                    </div>
                </div>

                {{-- 5. المخزون وSKU --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6" x-show="!form.has_variants">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">5</span>
                        المخزون والمعرف
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">الكمية في المخزون</label>
                            <input type="number" x-model="form.stock_quantity" min="0" placeholder="0"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">حد التنبيه (الكمية الدنيا)</label>
                            <input type="number" x-model="form.low_stock_threshold" min="0" placeholder="5"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU (رمز المنتج)</label>
                            <input type="text" x-model="form.sku" placeholder="ABC-001"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 mt-4 cursor-pointer select-none">
                        <input type="checkbox" x-model="form.track_inventory" class="w-4 h-4 rounded text-primary-600">
                        <span class="text-sm text-gray-700">تتبع المخزون</span>
                    </label>
                </div>

                {{-- 6. المتغيرات (Shopify-style) --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-7 h-7 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm font-black">6</span>
                            المتغيرات (لون، مقاس، ...)
                        </h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="form.has_variants" class="sr-only peer" @change="onVariantsToggle()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <div x-show="form.has_variants" x-transition>
                        {{-- Options --}}
                        <div class="space-y-4 mb-6">
                            <template x-for="(option, oi) in options" :key="oi">
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">اسم الخيار (مثال: اللون، المقاس)</label>
                                            <input type="text" x-model="option.name" placeholder="اللون"
                                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none text-sm"
                                                @input="debouncedGenerate()">
                                        </div>
                                        <button type="button" @click="removeOption(oi)"
                                            class="mt-5 p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(val, vi) in option.values" :key="vi">
                                            <span class="flex items-center gap-1 px-3 py-1 bg-gray-100 rounded-lg text-sm font-medium text-gray-700">
                                                <span x-text="val"></span>
                                                <button type="button" @click="removeValue(oi, vi)" class="text-gray-400 hover:text-red-500 transition-colors">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </span>
                                        </template>
                                        <input type="text"
                                            :placeholder="option.name ? 'أضف ' + option.name + '...' : 'أضف قيمة...'"
                                            class="px-3 py-1 bg-gray-50 border border-dashed border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary-400 min-w-[120px]"
                                            @keydown.enter.prevent="addValue(oi, $event.target.value); $event.target.value = ''"
                                            @keydown.comma.prevent="addValue(oi, $event.target.value); $event.target.value = ''">
                                    </div>
                                    <p class="text-xs text-gray-400">اكتب القيمة ثم اضغط Enter أو فاصلة لإضافتها</p>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addOption()"
                            class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50 transition-colors text-sm font-medium mb-6">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            إضافة خيار آخر (حتى 3 خيارات)
                        </button>

                        {{-- Variants table --}}
                        <div x-show="variants.length > 0" x-transition>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-700 text-sm"><span x-text="variants.length"></span> متغير</h3>
                                <button type="button" @click="generateVariants()" class="text-xs text-primary-600 hover:underline">إعادة توليد</button>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-gray-200">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="text-right px-4 py-3 font-semibold text-gray-600">المتغير</th>
                                            <th class="text-right px-4 py-3 font-semibold text-gray-600">السعر</th>
                                            <th class="text-right px-4 py-3 font-semibold text-gray-600">المخزون</th>
                                            <th class="text-right px-4 py-3 font-semibold text-gray-600">SKU</th>
                                            <th class="text-right px-4 py-3 font-semibold text-gray-600">متاح</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(variant, vi) in variants" :key="vi">
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 font-medium text-gray-800" x-text="variant.name"></td>
                                                <td class="px-4 py-3">
                                                    <input type="number" x-model="variant.price" :placeholder="form.price || '0'"
                                                        min="0" step="1"
                                                        class="w-24 px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none bg-gray-50">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" x-model="variant.stock_quantity"
                                                        min="0" placeholder="0"
                                                        class="w-20 px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none bg-gray-50"
                                                        :class="variant.stock_quantity <= 5 && variant.stock_quantity > 0 ? 'border-orange-300' : (variant.stock_quantity == 0 ? 'border-red-300' : '')">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="text" x-model="variant.sku" placeholder="SKU"
                                                        class="w-24 px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none bg-gray-50">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" x-model="variant.is_active" class="sr-only peer">
                                                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                                                    </label>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">اتركِ السعر فارغاً لاستخدام السعر الأساسي للمنتج</p>
                        </div>

                        <div x-show="form.has_variants && options.length === 0" class="text-center py-6 text-gray-400 text-sm">
                            أضف خياراً واحداً على الأقل (مثل: اللون) لتوليد المتغيرات
                        </div>
                    </div>

                    <div x-show="!form.has_variants" class="text-sm text-gray-500 bg-gray-50 rounded-xl p-4">
                        فعّل المتغيرات إذا كان المنتج يأتي بألوان أو مقاسات أو خصائص متعددة
                    </div>
                </div>

            </div>{{-- end main column --}}

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- الحالة --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4">الحالة</h2>
                    <select x-model="form.status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        <option value="active">نشط</option>
                        <option value="draft">مسودة</option>
                        <option value="archived">مؤرشف</option>
                    </select>
                    <label class="flex items-center gap-2 mt-4 cursor-pointer select-none">
                        <input type="checkbox" x-model="form.is_featured" class="w-4 h-4 rounded text-primary-600">
                        <span class="text-sm text-gray-700">منتج مميز (يظهر في الصفحة الرئيسية)</span>
                    </label>
                </div>

                {{-- التصنيف --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4">الفئة</h2>
                    <select x-model="form.category_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        <option value="">بدون فئة</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_ar ?? $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- الشحن --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-800 mb-4">الشحن</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">نوع الشحن</label>
                            <select x-model="form.shipping_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                                <option value="standard">شحن عادي</option>
                                <option value="free">شحن مجاني</option>
                                <option value="fixed">شحن بسعر ثابت</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">الوزن (كجم)</label>
                            <input type="number" x-model="form.weight" min="0" step="0.1" placeholder="0.5"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- ملخص --}}
                <div class="bg-primary-50 rounded-2xl border border-primary-100 p-5" x-show="form.price > 0">
                    <h2 class="font-bold text-primary-800 mb-3 text-sm">ملخص سريع</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">سعر البيع</span>
                            <strong class="text-primary-700" x-text="Number(form.price).toLocaleString() + ' د.ج'"></strong>
                        </div>
                        <div class="flex justify-between" x-show="form.compare_at_price > 0">
                            <span class="text-gray-600">السعر الأصلي</span>
                            <span class="line-through text-gray-400" x-text="Number(form.compare_at_price).toLocaleString() + ' د.ج'"></span>
                        </div>
                        <div class="flex justify-between" x-show="!form.has_variants">
                            <span class="text-gray-600">المخزون</span>
                            <strong :class="form.stock_quantity <= 5 ? 'text-orange-500' : 'text-gray-800'" x-text="form.stock_quantity + ' وحدة'"></strong>
                        </div>
                        <div class="flex justify-between" x-show="form.has_variants">
                            <span class="text-gray-600">المتغيرات</span>
                            <strong class="text-gray-800" x-text="variants.length + ' متغير'"></strong>
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="space-y-3">
                    <button type="button" @click="form.status = 'active'; save()"
                        class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center gap-2" :disabled="saving">
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="saving ? 'جاري الحفظ...' : 'حفظ ونشر'"></span>
                    </button>
                    <button type="button" @click="form.status = 'draft'; save()"
                        class="w-full py-3 bg-white text-gray-700 font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors" :disabled="saving">
                        حفظ كمسودة
                    </button>
                </div>

            </div>{{-- end sidebar --}}

        </div>{{-- end grid --}}
    </div>{{-- end container --}}
</div>

<script>
function productForm() {
    const initialProduct = @json($isEdit ? json_decode($productJson) : null);
    const initialImages = {!! $imagesJson !!};
    const initialOptions = {!! $optionsJson !!};
    const initialVariants = {!! $variantsJson !!};
    const saveUrl = '{{ $saveUrl }}';
    const method = '{{ $method }}';
    const isEdit = {{ $isEdit ? 'true' : 'false' }};

    return {
        form: initialProduct ? { ...initialProduct } : {
            name_ar: '', name_fr: '', name: '',
            description_ar: '', description_fr: '', description: '',
            price: '', compare_at_price: '', cost_price: '',
            stock_quantity: 0, sku: '', low_stock_threshold: 5,
            track_inventory: true, has_variants: false,
            category_id: '', status: 'active', is_featured: false,
            discount_percent: 0, weight: '', shipping_type: 'standard',
        },
        images: initialImages.length ? initialImages : [],
        options: initialOptions.length ? initialOptions.map(o => ({ name: o.name, values: [...o.values] })) : [],
        variants: initialVariants.length ? initialVariants.map(v => ({ ...v })) : [],
        newImageUrl: '',
        uploadingCount: 0,
        saving: false,
        toast: { show: false, message: '', type: 'success' },
        generateTimer: null,

        init() {
            if (this.form.has_variants && this.options.length && !this.variants.length) {
                this.generateVariants();
            }
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        // ── Images ──────────────────────────────────────────
        async handleFileSelect(e) {
            const files = Array.from(e.target.files);
            for (const file of files) {
                await this.uploadFile(file);
            }
            e.target.value = '';
        },

        handleDrop(e) {
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/') || f.type.startsWith('video/'));
            files.forEach(f => this.uploadFile(f));
        },

        async uploadFile(file) {
            this.uploadingCount++;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const res = await fetch('/api/v1/dashboard/products/upload-media', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    this.images.push({ url: data.url, type: data.type });
                }
            } catch {
                this.showToast('فشل رفع الملف', 'error');
            } finally {
                this.uploadingCount--;
            }
        },

        addImageFromUrl() {
            const url = this.newImageUrl.trim();
            if (!url) return;
            this.images.push({ url, type: 'image' });
            this.newImageUrl = '';
        },

        removeImage(i) {
            this.images.splice(i, 1);
        },

        setPrimary(i) {
            const img = this.images.splice(i, 1)[0];
            this.images.unshift(img);
        },

        // ── Options & Variants ───────────────────────────────
        onVariantsToggle() {
            if (!this.form.has_variants) {
                this.options = [];
                this.variants = [];
            } else if (this.options.length === 0) {
                this.addOption();
            }
        },

        addOption() {
            if (this.options.length >= 3) return;
            this.options.push({ name: '', values: [] });
        },

        removeOption(i) {
            this.options.splice(i, 1);
            this.debouncedGenerate();
        },

        addValue(oi, val) {
            const v = val.trim();
            if (!v || this.options[oi].values.includes(v)) return;
            this.options[oi].values.push(v);
            this.debouncedGenerate();
        },

        removeValue(oi, vi) {
            this.options[oi].values.splice(vi, 1);
            this.debouncedGenerate();
        },

        debouncedGenerate() {
            clearTimeout(this.generateTimer);
            this.generateTimer = setTimeout(() => this.generateVariants(), 300);
        },

        generateVariants() {
            const validOptions = this.options.filter(o => o.name && o.values.length > 0);
            if (!validOptions.length) { this.variants = []; return; }

            // Cartesian product
            const combos = validOptions.reduce((acc, opt) => {
                if (!acc.length) return opt.values.map(v => ({ [opt.name]: v }));
                return acc.flatMap(combo => opt.values.map(v => ({ ...combo, [opt.name]: v })));
            }, []);

            // Preserve existing variants data by name
            const existing = {};
            this.variants.forEach(v => { existing[v.name] = v; });

            this.variants = combos.map(combo => {
                const name = Object.values(combo).join(' / ');
                return existing[name] || {
                    id: null,
                    name,
                    options: combo,
                    sku: '',
                    price: '',
                    stock_quantity: 0,
                    is_active: true,
                };
            });
        },

        // ── Save ──────────────────────────────────────────────
        async save() {
            if (!this.form.name_ar && !this.form.name) {
                this.showToast('الرجاء إدخال اسم المنتج', 'error');
                return;
            }
            if (!this.form.price) {
                this.showToast('الرجاء إدخال سعر المنتج', 'error');
                return;
            }

            this.saving = true;
            const payload = {
                ...this.form,
                images: this.images.map(img => img.url),
            };

            if (this.form.has_variants) {
                payload.options = this.options.filter(o => o.name && o.values.length);
                payload.variants = this.variants;
            }

            try {
                const res = await fetch(saveUrl, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message || 'تم الحفظ بنجاح', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard.products") }}';
                    }, 1200);
                } else {
                    const errorMsg = data.message || Object.values(data.errors || {}).flat().join('، ');
                    this.showToast(errorMsg || 'حدث خطأ', 'error');
                }
            } catch {
                this.showToast('تعذر الاتصال بالخادم', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
