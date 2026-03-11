@extends('layouts.dashboard')
@section('title', 'إدارة الشحن')

@section('content')
<div x-data="shippingManager()" x-init="init()">
    <!-- Tabs -->
    <div class="flex items-center gap-1 bg-white rounded-2xl border border-gray-100 p-1 mb-6 w-fit">
        <button @click="activeTab = 'companies'" :class="activeTab === 'companies' ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-medium transition-all">شركات الشحن</button>
        <button @click="activeTab = 'rates'" :class="activeTab === 'rates' ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-medium transition-all">أسعار التوصيل</button>
        <button @click="activeTab = 'free'" :class="activeTab === 'free' ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-medium transition-all">الشحن المجاني</button>
    </div>

    <!-- Companies Tab -->
    <div x-show="activeTab === 'companies'" x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">شركات الشحن</h2>
            <button @click="showCompanyModal = true; resetCompanyForm()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                إضافة شركة
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="company in companies" :key="company.id">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 card-hover">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                            <template x-if="company.logo"><img :src="company.logo" class="w-full h-full object-contain p-1"></template>
                            <template x-if="!company.logo"><svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg></template>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800" x-text="company.name"></h3>
                            <p class="text-sm text-gray-500" x-text="company.estimated_days ? company.estimated_days + ' أيام' : '-'"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span :class="company.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="company.is_active ? 'مفعّلة' : 'معطّلة'"></span>
                        <div class="flex gap-1">
                            <button @click="editCompany(company)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                            <button @click="deleteCompany(company.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Rates Tab -->
    <div x-show="activeTab === 'rates'" x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">أسعار التوصيل حسب الولاية</h2>
            <button @click="showRateModal = true; resetRateForm()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                إضافة سعر
            </button>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الشركة</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الولاية</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">توصيل للمنزل</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">توصيل للمكتب</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">المدة</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الحالة</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="rate in rates" :key="rate.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm font-medium text-gray-800" x-text="rate.company_name"></td>
                                <td class="px-6 py-3 text-sm text-gray-600" x-text="rate.wilaya_name || 'الكل'"></td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-800" x-text="rate.home_price + ' د.ج'"></td>
                                <td class="px-6 py-3 text-sm text-gray-600" x-text="rate.desk_price ? rate.desk_price + ' د.ج' : '-'"></td>
                                <td class="px-6 py-3 text-sm text-gray-600" x-text="rate.estimated_days ? rate.estimated_days + ' أيام' : '-'"></td>
                                <td class="px-6 py-3"><span :class="rate.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2 py-1 rounded-full" x-text="rate.is_active ? 'مفعّل' : 'معطّل'"></span></td>
                                <td class="px-6 py-3">
                                    <div class="flex gap-1">
                                        <button @click="editRate(rate)" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                        <button @click="deleteRate(rate.id)" class="p-1.5 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Free Shipping Tab -->
    <div x-show="activeTab === 'free'" x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">قواعد الشحن المجاني</h2>
            <button @click="showFreeModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                إضافة قاعدة
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="rule in freeRules" :key="rule.id">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 card-hover">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800" x-text="rule.wilaya_name || 'كل الولايات'"></h3>
                            <p class="text-sm text-gray-500">الحد الأدنى: <span class="font-bold text-green-600" x-text="rule.min_order_amount + ' د.ج'"></span></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span :class="rule.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="rule.is_active ? 'مفعّلة' : 'معطّلة'"></span>
                        <button @click="deleteFreeRule(rule.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function shippingManager() {
    return {
        activeTab: 'companies', companies: [], rates: [], freeRules: [],
        showCompanyModal: false, showRateModal: false, showFreeModal: false,
        companyForm: { name: '', logo: '', estimated_days: '', is_active: true },
        rateForm: { shipping_company_id: '', wilaya_id: '', home_price: '', desk_price: '', estimated_days: '', is_active: true },
        async init() { await this.loadCompanies(); await this.loadRates(); await this.loadFreeRules(); },
        resetCompanyForm() { this.companyForm = { name: '', logo: '', estimated_days: '', is_active: true }; },
        resetRateForm() { this.rateForm = { shipping_company_id: '', wilaya_id: '', home_price: '', desk_price: '', estimated_days: '', is_active: true }; },
        async loadCompanies() { try { const r = await fetch('/api/store/shipping/companies', { headers: { 'Accept': 'application/json' } }); this.companies = (await r.json()).data || []; } catch(e) {} },
        async loadRates() { try { const r = await fetch('/api/store/shipping/rates', { headers: { 'Accept': 'application/json' } }); this.rates = (await r.json()).data || []; } catch(e) {} },
        async loadFreeRules() { try { const r = await fetch('/api/store/shipping/free-rules', { headers: { 'Accept': 'application/json' } }); this.freeRules = (await r.json()).data || []; } catch(e) {} },
        editCompany(c) { this.companyForm = {...c}; this.showCompanyModal = true; },
        editRate(r) { this.rateForm = {...r}; this.showRateModal = true; },
        async deleteCompany(id) { if(!confirm('حذف هذه الشركة؟')) return; await fetch(`/api/store/shipping/companies/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadCompanies(); },
        async deleteRate(id) { if(!confirm('حذف هذا السعر؟')) return; await fetch(`/api/store/shipping/rates/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadRates(); },
        async deleteFreeRule(id) { if(!confirm('حذف هذه القاعدة؟')) return; await fetch(`/api/store/shipping/free-rules/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadFreeRules(); }
    }
}
</script>
@endpush
@endsection
