@extends('layouts.dashboard')
@section('title', 'إدارة النطاقات')

@section('content')
<div x-data="domainsManager()" x-init="loadDomains()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">النطاقات</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة نطاقات متجرك (Subdomain و Custom Domain)</p>
        </div>
        <button @click="showModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة نطاق مخصص
        </button>
    </div>

    <!-- Default Subdomain -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">النطاق الفرعي الافتراضي</p>
                    <p class="font-bold text-gray-800 text-lg" x-text="subdomain + '.vpshopdz.com'"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> مفعّل
                </span>
                <a :href="'https://' + subdomain + '.vpshopdz.com'" target="_blank" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Custom Domains -->
    <h3 class="font-bold text-gray-800 mb-4">النطاقات المخصصة</h3>
    <div class="space-y-4">
        <template x-for="domain in domains" :key="domain.id">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 card-hover">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-lg" x-text="domain.domain"></p>
                            <p class="text-sm text-gray-500" x-text="'تم الإضافة: ' + domain.created_at"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="{
                            'bg-green-100 text-green-700': domain.status === 'active',
                            'bg-amber-100 text-amber-700': domain.status === 'pending',
                            'bg-red-100 text-red-700': domain.status === 'failed'
                        }" class="text-xs font-bold px-3 py-1 rounded-full" x-text="domain.status === 'active' ? 'مفعّل' : domain.status === 'pending' ? 'قيد التحقق' : 'فشل'"></span>
                        <button @click="deleteDomain(domain.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
                <!-- DNS Instructions -->
                <div x-show="domain.status === 'pending'" class="mt-4 bg-amber-50 rounded-xl p-4 border border-amber-200">
                    <h4 class="font-bold text-amber-800 text-sm mb-2">إعدادات DNS المطلوبة:</h4>
                    <div class="bg-white rounded-lg p-3 font-mono text-sm space-y-1">
                        <p class="text-gray-600">النوع: <span class="font-bold text-gray-800">CNAME</span></p>
                        <p class="text-gray-600">الاسم: <span class="font-bold text-gray-800" x-text="domain.domain"></span></p>
                        <p class="text-gray-600">القيمة: <span class="font-bold text-gray-800">stores.vpshopdz.com</span></p>
                    </div>
                    <button @click="verifyDomain(domain.id)" class="mt-3 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">التحقق الآن</button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="domains.length === 0" class="text-center py-12 bg-white rounded-2xl border border-gray-100 mt-4">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد نطاقات مخصصة</h3>
        <p class="text-gray-500 mb-4">أضف نطاقك الخاص لمتجرك الاحترافي</p>
    </div>

    <!-- Add Domain Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800">إضافة نطاق مخصص</h3></div>
            <form @submit.prevent="addDomain()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">النطاق *</label>
                    <input type="text" x-model="newDomain" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="shop.example.com" dir="ltr">
                    <p class="text-xs text-gray-500 mt-1">مثال: shop.yourdomain.com أو yourdomain.com</p>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="saving" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium disabled:opacity-50">إضافة النطاق</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function domainsManager() {
    return {
        domains: [], subdomain: '{{ auth()->user()->store->slug ?? "" }}', showModal: false, newDomain: '', saving: false,
        async loadDomains() {
            try { const r = await fetch('/api/store/domains', { headers: { 'Accept': 'application/json' } }); this.domains = (await r.json()).data || []; } catch(e) {}
        },
        async addDomain() {
            this.saving = true;
            try { await fetch('/api/store/domains', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ domain: this.newDomain }) }); this.showModal = false; this.newDomain = ''; await this.loadDomains(); } catch(e) {}
            this.saving = false;
        },
        async verifyDomain(id) { await fetch(`/api/store/domains/${id}/verify`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadDomains(); },
        async deleteDomain(id) { if(!confirm('حذف هذا النطاق؟')) return; await fetch(`/api/store/domains/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadDomains(); }
    }
}
</script>
@endpush
@endsection
