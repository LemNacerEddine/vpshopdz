@extends('layouts.dashboard')
@section('title', 'إدارة العملاء')

@section('content')
<div x-data="customersManager()" x-init="loadCustomers()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">العملاء</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة قاعدة عملاء متجرك</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" x-model="search" @input.debounce.300ms="loadCustomers()" placeholder="بحث بالاسم أو الهاتف..." class="pr-10 pl-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none w-64">
            </div>
            <button @click="exportCustomers()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                تصدير
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">إجمالي العملاء</p>
            <p class="text-2xl font-black text-gray-800 mt-1" x-text="stats.total || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">عملاء جدد (هذا الشهر)</p>
            <p class="text-2xl font-black text-green-600 mt-1" x-text="stats.new_this_month || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">عملاء متكررون</p>
            <p class="text-2xl font-black text-blue-600 mt-1" x-text="stats.returning || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">متوسط قيمة الطلب</p>
            <p class="text-2xl font-black text-purple-600 mt-1" x-text="(stats.avg_order_value || 0) + ' د.ج'"></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">العميل</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الهاتف</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الولاية</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الطلبات</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">إجمالي المشتريات</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">آخر طلب</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="customer in customers" :key="customer.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm" x-text="customer.name?.charAt(0) || '?'"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800" x-text="customer.name"></p>
                                        <p class="text-xs text-gray-500" x-text="customer.email || '-'"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="customer.phone"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="customer.wilaya?.name_ar || '-'"></td>
                            <td class="px-6 py-4"><span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full" x-text="customer.orders_count || 0"></span></td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800" x-text="(customer.total_spent || 0) + ' د.ج'"></td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="customer.last_order_date || '-'"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <button @click="viewCustomer(customer)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-primary-600" title="عرض التفاصيل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <a :href="'https://wa.me/' + customer.phone" target="_blank" class="p-2 hover:bg-green-50 rounded-lg text-gray-500 hover:text-green-600" title="WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="customers.length === 0 && !loading" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <h3 class="text-lg font-bold text-gray-600 mb-2">لا يوجد عملاء</h3>
            <p class="text-gray-500">سيظهر العملاء هنا عند استلام أول طلب</p>
        </div>
    </div>

    <!-- Customer Detail Modal -->
    <div x-show="showDetail" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showDetail = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">تفاصيل العميل</h3>
                <button @click="showDetail = false" class="p-2 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-6" x-show="selectedCustomer">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><p class="text-sm text-gray-500">الاسم</p><p class="font-bold text-gray-800" x-text="selectedCustomer?.name"></p></div>
                    <div><p class="text-sm text-gray-500">الهاتف</p><p class="font-bold text-gray-800" x-text="selectedCustomer?.phone"></p></div>
                    <div><p class="text-sm text-gray-500">البريد</p><p class="font-bold text-gray-800" x-text="selectedCustomer?.email || '-'"></p></div>
                    <div><p class="text-sm text-gray-500">العنوان</p><p class="font-bold text-gray-800" x-text="selectedCustomer?.address || '-'"></p></div>
                </div>
                <h4 class="font-bold text-gray-800 mb-3">آخر الطلبات</h4>
                <div class="space-y-2">
                    <template x-for="order in selectedCustomer?.recent_orders || []" :key="order.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div>
                                <span class="font-medium text-gray-800" x-text="'#' + order.order_number"></span>
                                <span class="text-sm text-gray-500 mr-2" x-text="order.created_at"></span>
                            </div>
                            <div class="text-left">
                                <span class="font-bold text-gray-800" x-text="order.total + ' د.ج'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function customersManager() {
    return {
        customers: [], loading: true, search: '', showDetail: false, selectedCustomer: null,
        stats: { total: 0, new_this_month: 0, returning: 0, avg_order_value: 0 },
        async loadCustomers() {
            try {
                const res = await fetch(`/api/store/customers?search=${this.search}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.customers = data.data || [];
                this.stats = data.stats || this.stats;
            } catch (e) { console.error(e); }
            this.loading = false;
        },
        viewCustomer(customer) { this.selectedCustomer = customer; this.showDetail = true; },
        exportCustomers() { window.open('/api/store/customers/export?format=csv', '_blank'); }
    }
}
</script>
@endpush
@endsection
