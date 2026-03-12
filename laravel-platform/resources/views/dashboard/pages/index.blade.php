@extends('layouts.dashboard')
@section('title', 'الصفحات')

@section('content')
<div x-data="pagesManager()" x-init="loadPages()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">الصفحات</h2>
            <p class="text-sm text-gray-500 mt-1">إنشاء صفحات مخصصة (سياسة الخصوصية، من نحن، إلخ)</p>
        </div>
        <button @click="showModal = true; editMode = false; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إنشاء صفحة
        </button>
    </div>

    <div class="space-y-3">
        <template x-for="page in pages" :key="page.id">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="page.title_ar"></h3>
                        <p class="text-sm text-gray-500" x-text="'/page/' + page.slug"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span :class="page.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="page.is_active ? 'منشورة' : 'مسودة'"></span>
                    <button @click="editPage(page)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <button @click="deletePage(page.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="pages.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد صفحات</h3>
        <p class="text-gray-500 mb-4">أنشئ صفحات مثل "من نحن" و"سياسة الخصوصية"</p>
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'تعديل الصفحة' : 'إنشاء صفحة جديدة'"></h3></div>
            <form @submit.prevent="savePage()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">العنوان بالعربية *</label><input type="text" x-model="form.title_ar" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">العنوان بالفرنسية</label><input type="text" x-model="form.title_fr" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">الرابط (slug)</label><input type="text" x-model="form.slug" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="about-us"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">المحتوى بالعربية *</label><textarea x-model="form.content_ar" rows="8" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none" placeholder="اكتب محتوى الصفحة هنا... (يدعم HTML)"></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">المحتوى بالفرنسية</label><textarea x-model="form.content_fr" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none"></textarea></div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" x-model="form.is_active" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div></label>
                    <span class="text-sm font-medium text-gray-700">نشر الصفحة</span>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium" x-text="editMode ? 'حفظ التعديلات' : 'إنشاء الصفحة'"></button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pagesManager() {
    return {
        pages: [], loading: true, showModal: false, editMode: false, editId: null,
        form: { title_ar: '', title_fr: '', slug: '', content_ar: '', content_fr: '', is_active: true },
        resetForm() { this.form = { title_ar: '', title_fr: '', slug: '', content_ar: '', content_fr: '', is_active: true }; this.editId = null; },
        async loadPages() { try { const r = await fetch('/api/v1/dashboard/pages', { headers: { 'Accept': 'application/json' } }); this.pages = (await r.json()).data || []; } catch(e) {} this.loading = false; },
        editPage(p) { this.editMode = true; this.editId = p.id; this.form = {...p}; this.showModal = true; },
        async savePage() {
            const url = this.editMode ? `/api/v1/dashboard/pages/${this.editId}` : '/api/v1/dashboard/pages';
            await fetch(url, { method: this.editMode ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) });
            this.showModal = false; this.resetForm(); await this.loadPages();
        },
        async deletePage(id) { if(!confirm('حذف هذه الصفحة؟')) return; await fetch(`/api/v1/dashboard/pages/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadPages(); }
    }
}
</script>
@endpush
@endsection
