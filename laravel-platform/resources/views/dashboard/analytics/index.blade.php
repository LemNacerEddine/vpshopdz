@extends('layouts.dashboard')
@section('title', 'الإحصائيات')

@section('content')
<div x-data="analyticsManager()" x-init="loadAnalytics()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">الإحصائيات والتحليلات</h2>
            <p class="text-sm text-gray-500 mt-1">تتبع أداء متجرك بالتفصيل</p>
        </div>
        <div class="flex items-center gap-2">
            <select x-model="period" @change="loadAnalytics()" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none text-sm">
                <option value="today">اليوم</option>
                <option value="week">هذا الأسبوع</option>
                <option value="month" selected>هذا الشهر</option>
                <option value="year">هذه السنة</option>
            </select>
        </div>
    </div>

    <!-- Main Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></div>
                <span class="text-xs font-bold px-2 py-1 rounded-lg" :class="stats.visitors_change >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'" x-text="(stats.visitors_change >= 0 ? '+' : '') + stats.visitors_change + '%'"></span>
            </div>
            <p class="text-3xl font-black text-gray-800" x-text="stats.visitors || 0"></p>
            <p class="text-sm text-gray-500">الزوار</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <span class="text-xs font-bold px-2 py-1 rounded-lg" :class="stats.revenue_change >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'" x-text="(stats.revenue_change >= 0 ? '+' : '') + stats.revenue_change + '%'"></span>
            </div>
            <p class="text-3xl font-black text-gray-800" x-text="(stats.revenue || 0).toLocaleString()"></p>
            <p class="text-sm text-gray-500">الإيرادات (د.ج)</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
            </div>
            <p class="text-3xl font-black text-gray-800" x-text="stats.orders || 0"></p>
            <p class="text-sm text-gray-500">الطلبات</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
            </div>
            <p class="text-3xl font-black text-gray-800" x-text="(stats.conversion_rate || 0) + '%'"></p>
            <p class="text-sm text-gray-500">معدل التحويل</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">الإيرادات</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
        <!-- Orders Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">الطلبات</h3>
            <canvas id="ordersChart" height="200"></canvas>
        </div>
    </div>

    <!-- More Stats -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Top Products -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">أكثر المنتجات مبيعاً</h3>
            <div class="space-y-3">
                <template x-for="(product, index) in topProducts" :key="index">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 bg-primary-100 rounded-lg flex items-center justify-center text-xs font-bold text-primary-600" x-text="index + 1"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="product.name"></p>
                            <p class="text-xs text-gray-500" x-text="product.sold + ' مبيعات'"></p>
                        </div>
                        <span class="text-sm font-bold text-gray-800" x-text="product.revenue + ' د.ج'"></span>
                    </div>
                </template>
                <div x-show="topProducts.length === 0" class="text-center py-4 text-gray-400 text-sm">لا توجد بيانات</div>
            </div>
        </div>

        <!-- Top Wilayas -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">أكثر الولايات طلباً</h3>
            <div class="space-y-3">
                <template x-for="(wilaya, index) in topWilayas" :key="index">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-xs font-bold text-blue-600" x-text="index + 1"></span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800" x-text="wilaya.name"></p>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                                <div class="bg-blue-500 h-1.5 rounded-full" :style="'width:' + wilaya.percentage + '%'"></div>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-800" x-text="wilaya.orders"></span>
                    </div>
                </template>
                <div x-show="topWilayas.length === 0" class="text-center py-4 text-gray-400 text-sm">لا توجد بيانات</div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">توزيع حالات الطلبات</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
function analyticsManager() {
    return {
        period: 'month',
        stats: { visitors: 0, visitors_change: 0, revenue: 0, revenue_change: 0, orders: 0, conversion_rate: 0 },
        topProducts: [], topWilayas: [],
        async loadAnalytics() {
            try {
                const r = await fetch(`/api/store/analytics?period=${this.period}`, { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                this.stats = d.stats || this.stats;
                this.topProducts = d.top_products || [];
                this.topWilayas = d.top_wilayas || [];
                this.renderCharts(d);
            } catch(e) { this.renderCharts({}); }
        },
        renderCharts(data) {
            const labels = data.chart_labels || ['أسبوع 1', 'أسبوع 2', 'أسبوع 3', 'أسبوع 4'];
            // Revenue Chart
            const rc = document.getElementById('revenueChart');
            if (rc._chart) rc._chart.destroy();
            rc._chart = new Chart(rc, { type: 'line', data: { labels, datasets: [{ label: 'الإيرادات', data: data.revenue_data || [0,0,0,0], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            // Orders Chart
            const oc = document.getElementById('ordersChart');
            if (oc._chart) oc._chart.destroy();
            oc._chart = new Chart(oc, { type: 'bar', data: { labels, datasets: [{ label: 'الطلبات', data: data.orders_data || [0,0,0,0], backgroundColor: '#6366f1', borderRadius: 8 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            // Status Chart
            const sc = document.getElementById('statusChart');
            if (sc._chart) sc._chart.destroy();
            const statusData = data.status_distribution || { pending: 0, confirmed: 0, shipped: 0, delivered: 0, cancelled: 0 };
            sc._chart = new Chart(sc, { type: 'doughnut', data: { labels: ['جديدة', 'مؤكدة', 'قيد الشحن', 'تم التسليم', 'ملغاة'], datasets: [{ data: [statusData.pending, statusData.confirmed, statusData.shipped, statusData.delivered, statusData.cancelled], backgroundColor: ['#f59e0b', '#10b981', '#6366f1', '#22c55e', '#ef4444'] }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }
    }
}
</script>
@endpush
@endsection
