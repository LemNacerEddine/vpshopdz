@extends('layouts.dashboard')
@section('title', 'السلات المتروكة')

@section('content')
<div x-data="abandonedCartsManager()" x-init="loadCarts()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">السلات المتروكة</h2>
            <p class="text-sm text-gray-500 mt-1">استرجع العملاء الذين لم يكملوا طلباتهم</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg></div>
                <div>
                    <p class="text-2xl font-black text-gray-800" x-text="stats.total || 0"></p>
                    <p class="text-xs text-gray-500">سلات متروكة</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <div>
                    <p class="text-2xl font-black text-red-600" x-text="(stats.lost_revenue || 0) + ' د.ج'"></p>
                    <p class="text-xs text-gray-500">إيرادات مفقودة</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <div>
                    <p class="text-2xl font-black text-green-600" x-text="stats.recovered || 0"></p>
                    <p class="text-xs text-gray-500">تم استرجاعها</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
                <div>
                    <p class="text-2xl font-black text-blue-600" x-text="(stats.recovery_rate || 0) + '%'"></p>
                    <p class="text-xs text-gray-500">نسبة الاسترجاع</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">العميل</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">المنتجات</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">القيمة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">التاريخ</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الحالة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="cart in carts" :key="cart.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800" x-text="cart.customer_name || 'زائر'"></p>
                                <p class="text-xs text-gray-500" x-text="cart.customer_phone || cart.customer_email || '-'"></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="(cart.items_count || 0) + ' منتج'"></td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800" x-text="(cart.total || 0) + ' د.ج'"></td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="cart.created_at"></td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-amber-100 text-amber-700': cart.status === 'abandoned',
                                    'bg-blue-100 text-blue-700': cart.status === 'contacted',
                                    'bg-green-100 text-green-700': cart.status === 'recovered'
                                }" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="cart.status === 'abandoned' ? 'متروكة' : cart.status === 'contacted' ? 'تم التواصل' : 'تم الاسترجاع'"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <button x-show="cart.customer_phone" @click="sendWhatsApp(cart)" class="p-2 hover:bg-green-50 rounded-lg text-gray-500 hover:text-green-600" title="إرسال WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                    </button>
                                    <button @click="markRecovered(cart.id)" x-show="cart.status !== 'recovered'" class="p-2 hover:bg-green-50 rounded-lg text-gray-500 hover:text-green-600" title="تم الاسترجاع">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="carts.length === 0 && !loading" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
            <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد سلات متروكة</h3>
            <p class="text-gray-500">ممتاز! كل العملاء أكملوا طلباتهم</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function abandonedCartsManager() {
    return {
        carts: [], loading: true,
        stats: { total: 0, lost_revenue: 0, recovered: 0, recovery_rate: 0 },
        headers() { return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }; },
        async loadCarts() {
            try { const r = await fetch('/api/v1/dashboard/abandoned-carts', { headers: this.headers() }); const d = await r.json(); this.carts = d.data || []; this.stats = d.stats || this.stats; } catch(e) {}
            this.loading = false;
        },
        sendWhatsApp(cart) { const msg = encodeURIComponent(`مرحباً ${cart.customer_name}، لاحظنا أنك لم تكمل طلبك. هل تحتاج مساعدة؟`); window.open(`https://wa.me/${cart.customer_phone}?text=${msg}`, '_blank'); },
        async markRecovered(id) { await fetch(`/api/v1/dashboard/abandoned-carts/${id}/recover`, { method: 'POST', headers: this.headers() }); await this.loadCarts(); }
    }
}
</script>
@endpush
@endsection
