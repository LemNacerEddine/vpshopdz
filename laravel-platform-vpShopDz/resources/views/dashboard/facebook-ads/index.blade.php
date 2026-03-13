@extends('layouts.dashboard')
@section('title', 'إعلانات Facebook')

@section('content')
<div x-data="facebookAdsManager()" x-init="loadAds()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">إعلانات Facebook</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة وتتبع حملاتك الإعلانية</p>
        </div>
        <button @click="showModal = true; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-lg shadow-blue-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة حملة
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">إجمالي الإنفاق</p>
            <p class="text-2xl font-black text-gray-800 mt-1" x-text="(stats.total_spend || 0) + ' د.ج'"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">الطلبات من الإعلانات</p>
            <p class="text-2xl font-black text-green-600 mt-1" x-text="stats.total_orders || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">إيرادات الإعلانات</p>
            <p class="text-2xl font-black text-blue-600 mt-1" x-text="(stats.total_revenue || 0) + ' د.ج'"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">ROAS</p>
            <p class="text-2xl font-black mt-1" :class="(stats.roas || 0) >= 1 ? 'text-green-600' : 'text-red-600'" x-text="(stats.roas || 0).toFixed(2) + 'x'"></p>
        </div>
    </div>

    <!-- Ads Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الحملة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الإنفاق</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الطلبات</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الإيرادات</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">ROAS</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الحالة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="ad in ads" :key="ad.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800" x-text="ad.name"></p>
                                <p class="text-xs text-gray-500" x-text="ad.platform_campaign_id || '-'"></p>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800" x-text="(ad.spend || 0) + ' د.ج'"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="ad.orders_count || 0"></td>
                            <td class="px-6 py-4 text-sm font-bold text-green-600" x-text="(ad.revenue || 0) + ' د.ج'"></td>
                            <td class="px-6 py-4 text-sm font-bold" :class="ad.roas >= 1 ? 'text-green-600' : 'text-red-600'" x-text="(ad.roas || 0).toFixed(2) + 'x'"></td>
                            <td class="px-6 py-4"><span :class="ad.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="ad.is_active ? 'نشطة' : 'متوقفة'"></span></td>
                            <td class="px-6 py-4">
                                <div class="flex gap-1">
                                    <button @click="editAd(ad)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                    <button @click="deleteAd(ad.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="ads.length === 0 && !loading" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
            <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد حملات</h3>
            <p class="text-gray-500">أضف حملاتك لتتبع أدائها</p>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'تعديل الحملة' : 'إضافة حملة'"></h3></div>
            <form @submit.prevent="saveAd()" class="p-6 space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">اسم الحملة *</label><input type="text" x-model="form.name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">معرّف الحملة (Facebook)</label><input type="text" x-model="form.platform_campaign_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" dir="ltr"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">الإنفاق (د.ج)</label><input type="number" x-model="form.spend" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">UTM Source</label><input type="text" x-model="form.utm_source" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" dir="ltr" placeholder="facebook"></div>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">حفظ</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function facebookAdsManager() {
    return {
        ads: [], loading: true, showModal: false, editMode: false, editId: null,
        stats: { total_spend: 0, total_orders: 0, total_revenue: 0, roas: 0 },
        form: { name: '', platform_campaign_id: '', spend: 0, utm_source: 'facebook' },
        resetForm() { this.form = { name: '', platform_campaign_id: '', spend: 0, utm_source: 'facebook' }; this.editMode = false; this.editId = null; },
        async loadAds() { try { const r = await fetch('/api/v1/dashboard/facebook-ads', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.ads = d.data || []; this.stats = d.stats || this.stats; } catch(e) {} this.loading = false; },
        editAd(ad) { this.editMode = true; this.editId = ad.id; this.form = {...ad}; this.showModal = true; },
        async saveAd() {
            const url = this.editMode ? `/api/v1/dashboard/facebook-ads/${this.editId}` : '/api/v1/dashboard/facebook-ads';
            await fetch(url, { method: this.editMode ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) });
            this.showModal = false; this.resetForm(); await this.loadAds();
        },
        async deleteAd(id) { if(!confirm('حذف هذه الحملة؟')) return; await fetch(`/api/v1/dashboard/facebook-ads/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadAds(); }
    }
}
</script>
@endpush
@endsection
