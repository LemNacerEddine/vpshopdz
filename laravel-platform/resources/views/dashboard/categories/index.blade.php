@extends('layouts.dashboard')
@section('title', 'إدارة الفئات')

@section('content')
<div x-data="categoriesManager()" x-init="loadCategories()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">الفئات</h2>
            <p class="text-sm text-gray-500 mt-1">تنظيم المنتجات في فئات لتسهيل التصفح</p>
        </div>
        <button @click="showModal = true; editMode = false; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-all font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة فئة
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="category in categories" :key="category.id">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
                <div class="h-32 bg-gradient-to-br from-primary-100 to-primary-50 flex items-center justify-center relative">
                    <template x-if="category.image">
                        <img :src="category.image" class="w-full h-full object-cover" :alt="category.name_ar">
                    </template>
                    <template x-if="!category.image">
                        <svg class="w-12 h-12 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </template>
                    <div class="absolute top-3 left-3 flex gap-2">
                        <span class="bg-white/90 backdrop-blur-sm text-xs font-bold px-2 py-1 rounded-lg" x-text="category.products_count + ' منتج'"></span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-gray-800" x-text="category.name_ar"></h3>
                    <p class="text-sm text-gray-500 mt-1" x-text="category.name_fr || '-'"></p>
                    <div class="flex items-center justify-between mt-4">
                        <span :class="category.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="category.is_active ? 'مفعّلة' : 'معطّلة'"></span>
                        <div class="flex items-center gap-1">
                            <button @click="editCategory(category)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-primary-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click="deleteCategory(category.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="categories.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد فئات</h3>
        <p class="text-gray-500 mb-4">أضف فئات لتنظيم منتجاتك</p>
        <button @click="showModal = true; editMode = false; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة فئة جديدة
        </button>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'تعديل الفئة' : 'إضافة فئة جديدة'"></h3>
            </div>
            <form @submit.prevent="saveCategory()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالعربية *</label>
                        <input type="text" x-model="form.name_ar" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" placeholder="مثال: إلكترونيات">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالفرنسية</label>
                        <input type="text" x-model="form.name_fr" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" placeholder="Ex: Électronique">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالإنجليزية</label>
                    <input type="text" x-model="form.name_en" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" placeholder="Ex: Electronics">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                    <textarea x-model="form.description" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none" placeholder="وصف مختصر للفئة"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رابط الصورة</label>
                    <input type="url" x-model="form.image" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" placeholder="https://...">
                </div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">فئة مفعّلة</span>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="saving" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium disabled:opacity-50">
                        <span x-show="!saving" x-text="editMode ? 'حفظ التعديلات' : 'إضافة الفئة'"></span>
                        <span x-show="saving">جاري الحفظ...</span>
                    </button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function categoriesManager() {
    return {
        categories: [],
        loading: true,
        showModal: false,
        editMode: false,
        saving: false,
        editId: null,
        form: { name_ar: '', name_fr: '', name_en: '', description: '', image: '', is_active: true },
        
        resetForm() {
            this.form = { name_ar: '', name_fr: '', name_en: '', description: '', image: '', is_active: true };
            this.editId = null;
        },
        
        async loadCategories() {
            try {
                const res = await fetch('/api/store/categories', { headers: { 'Authorization': 'Bearer ' + (document.cookie.match(/token=([^;]+)/)?.[1] || ''), 'Accept': 'application/json' } });
                const data = await res.json();
                this.categories = data.data || [];
            } catch (e) { console.error(e); }
            this.loading = false;
        },
        
        editCategory(cat) {
            this.editMode = true;
            this.editId = cat.id;
            this.form = { name_ar: cat.name_ar, name_fr: cat.name_fr || '', name_en: cat.name_en || '', description: cat.description || '', image: cat.image || '', is_active: cat.is_active };
            this.showModal = true;
        },
        
        async saveCategory() {
            this.saving = true;
            try {
                const url = this.editMode ? `/api/store/categories/${this.editId}` : '/api/store/categories';
                const method = this.editMode ? 'PUT' : 'POST';
                await fetch(url, {
                    method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.form)
                });
                this.showModal = false;
                this.resetForm();
                await this.loadCategories();
            } catch (e) { console.error(e); }
            this.saving = false;
        },
        
        async deleteCategory(id) {
            if (!confirm('هل أنت متأكد من حذف هذه الفئة؟')) return;
            try {
                await fetch(`/api/store/categories/${id}`, {
                    method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                await this.loadCategories();
            } catch (e) { console.error(e); }
        }
    }
}
</script>
@endpush
@endsection
