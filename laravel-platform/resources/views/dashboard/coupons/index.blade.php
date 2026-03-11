@extends('layouts.dashboard')
@section('title', 'إدارة الكوبونات')

@section('content')
<div x-data="couponsManager()" x-init="loadCoupons()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">الكوبونات</h2>
            <p class="text-sm text-gray-500 mt-1">إنشاء وإدارة أكواد الخصم</p>
        </div>
        <button @click="showModal = true; editMode = false; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إنشاء كوبون
        </button>
    </div>

    <!-- Coupons Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="coupon in coupons" :key="coupon.id">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover relative">
                <div class="absolute top-3 left-3">
                    <span :class="coupon.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="coupon.is_active ? 'مفعّل' : 'معطّل'"></span>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-lg text-gray-800 tracking-wider" x-text="coupon.code"></h3>
                            <p class="text-sm text-gray-500" x-text="coupon.type === 'percentage' ? coupon.value + '% خصم' : coupon.value + ' د.ج خصم'"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-500">مرات الاستخدام</p>
                            <p class="text-lg font-bold text-gray-800" x-text="coupon.used_count || 0"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-500">الحد الأقصى</p>
                            <p class="text-lg font-bold text-gray-800" x-text="coupon.max_uses || '∞'"></p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-4">
                        <p x-show="coupon.min_order_amount">الحد الأدنى: <span class="font-bold" x-text="coupon.min_order_amount + ' د.ج'"></span></p>
                        <p x-show="coupon.expires_at">ينتهي: <span class="font-bold" x-text="coupon.expires_at"></span></p>
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <button @click="copyCode(coupon.code)" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 text-sm font-medium flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            نسخ
                        </button>
                        <button @click="editCoupon(coupon)" class="p-2 hover:bg-gray-100 rounded-xl text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                        <button @click="deleteCoupon(coupon.id)" class="p-2 hover:bg-red-50 rounded-xl text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="coupons.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد كوبونات</h3>
        <p class="text-gray-500 mb-4">أنشئ كوبونات خصم لجذب المزيد من العملاء</p>
        <button @click="showModal = true; editMode = false; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700">إنشاء كوبون</button>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'تعديل الكوبون' : 'إنشاء كوبون جديد'"></h3>
            </div>
            <form @submit.prevent="saveCoupon()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">كود الكوبون *</label>
                        <input type="text" x-model="form.code" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none uppercase" placeholder="SAVE20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع الخصم *</label>
                        <select x-model="form.type" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت (د.ج)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">القيمة *</label>
                        <input type="number" x-model="form.value" required min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأقصى للاستخدام</label>
                        <input type="number" x-model="form.max_uses" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="غير محدود">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأدنى للطلب</label>
                        <input type="number" x-model="form.min_order_amount" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الانتهاء</label>
                        <input type="date" x-model="form.expires_at" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">كوبون مفعّل</span>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="saving" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium disabled:opacity-50">
                        <span x-show="!saving" x-text="editMode ? 'حفظ التعديلات' : 'إنشاء الكوبون'"></span>
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
function couponsManager() {
    return {
        coupons: [], loading: true, showModal: false, editMode: false, saving: false, editId: null,
        form: { code: '', type: 'percentage', value: '', max_uses: '', min_order_amount: '', expires_at: '', is_active: true },
        resetForm() { this.form = { code: '', type: 'percentage', value: '', max_uses: '', min_order_amount: '', expires_at: '', is_active: true }; this.editId = null; },
        async loadCoupons() {
            try { const r = await fetch('/api/store/coupons', { headers: { 'Accept': 'application/json' } }); this.coupons = (await r.json()).data || []; } catch(e) {}
            this.loading = false;
        },
        editCoupon(c) { this.editMode = true; this.editId = c.id; this.form = {...c}; this.showModal = true; },
        async saveCoupon() {
            this.saving = true;
            try {
                const url = this.editMode ? `/api/store/coupons/${this.editId}` : '/api/store/coupons';
                await fetch(url, { method: this.editMode ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) });
                this.showModal = false; this.resetForm(); await this.loadCoupons();
            } catch(e) {}
            this.saving = false;
        },
        async deleteCoupon(id) { if(!confirm('حذف هذا الكوبون؟')) return; await fetch(`/api/store/coupons/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadCoupons(); },
        copyCode(code) { navigator.clipboard.writeText(code); }
    }
}
</script>
@endpush
@endsection
