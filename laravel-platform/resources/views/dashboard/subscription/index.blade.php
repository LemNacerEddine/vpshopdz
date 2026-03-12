@extends('layouts.dashboard')
@section('title', 'الاشتراك')

@section('content')
<div x-data="subscriptionManager()" x-init="loadSubscription()">
    <!-- Current Plan -->
    <div class="bg-gradient-to-l from-primary-600 to-primary-700 rounded-2xl p-6 text-white mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full translate-x-1/4 translate-y-1/4"></div>
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-white/70 text-sm mb-1">خطتك الحالية</p>
                    <h2 class="text-3xl font-black" x-text="current.plan_name || 'مجاني'"></h2>
                    <p class="text-white/80 mt-2" x-show="current.expires_at">
                        تنتهي في: <span class="font-bold" x-text="current.expires_at"></span>
                    </p>
                </div>
                <div class="text-left">
                    <p class="text-white/70 text-sm mb-1">المنتجات المستخدمة</p>
                    <p class="text-2xl font-black"><span x-text="current.products_used || 0"></span> / <span x-text="current.products_limit || '∞'"></span></p>
                    <div class="w-48 bg-white/20 rounded-full h-2 mt-2">
                        <div class="bg-white h-2 rounded-full transition-all" :style="'width:' + Math.min((current.products_used / (current.products_limit || 1)) * 100, 100) + '%'"></div>
                    </div>
                </div>
            </div>
            <!-- Usage Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-white/20">
                <div>
                    <p class="text-white/60 text-xs">الطلبات</p>
                    <p class="text-lg font-bold"><span x-text="current.orders_used || 0"></span> / <span x-text="current.orders_limit || '∞'"></span></p>
                </div>
                <div>
                    <p class="text-white/60 text-xs">أعضاء الفريق</p>
                    <p class="text-lg font-bold"><span x-text="current.staff_used || 0"></span> / <span x-text="current.staff_limit || '∞'"></span></p>
                </div>
                <div>
                    <p class="text-white/60 text-xs">التخزين</p>
                    <p class="text-lg font-bold"><span x-text="current.storage_used || '0 MB'"></span> / <span x-text="current.storage_limit || '∞'"></span></p>
                </div>
                <div>
                    <p class="text-white/60 text-xs">النطاقات المخصصة</p>
                    <p class="text-lg font-bold"><span x-text="current.domains_used || 0"></span> / <span x-text="current.domains_limit || '∞'"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans -->
    <h3 class="text-xl font-bold text-gray-800 mb-4">الخطط المتاحة</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
        <template x-for="plan in plans" :key="plan.id">
            <div class="bg-white rounded-2xl border-2 p-5 relative" :class="plan.is_popular ? 'border-primary-500 shadow-lg shadow-primary-500/20' : plan.slug === current.plan_slug ? 'border-primary-300' : 'border-gray-100'">
                <div x-show="plan.is_popular" class="absolute -top-3 right-4 bg-primary-600 text-white text-xs font-bold px-3 py-1 rounded-full">الأكثر طلباً</div>
                <div x-show="plan.slug === current.plan_slug" class="absolute -top-3 left-4 bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">خطتك</div>
                <h4 class="font-bold text-gray-800 text-lg mb-1" x-text="plan.name"></h4>
                <div class="mb-4">
                    <span class="text-3xl font-black text-gray-800" x-text="plan.price === 0 ? 'مجاني' : plan.price"></span>
                    <span x-show="plan.price > 0" class="text-sm text-gray-500"> د.ج/شهر</span>
                </div>
                <ul class="space-y-2 mb-5 text-sm">
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="(plan.max_products === -1 ? 'غير محدود' : plan.max_products) + ' منتج'"></span>
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="(plan.max_orders === -1 ? 'غير محدود' : plan.max_orders) + ' طلب/شهر'"></span>
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 flex-shrink-0" :class="plan.custom_domain ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span :class="!plan.custom_domain && 'text-gray-400'">نطاق مخصص</span>
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 flex-shrink-0" :class="plan.premium_themes ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span :class="!plan.premium_themes && 'text-gray-400'">ثيمات Premium</span>
                    </li>
                </ul>
                <button x-show="plan.slug !== current.plan_slug" @click="upgradePlan(plan)" class="w-full px-4 py-2.5 rounded-xl font-medium text-sm" :class="plan.is_popular ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" x-text="plan.price > (current.plan_price || 0) ? 'ترقية' : 'تغيير'"></button>
                <button x-show="plan.slug === current.plan_slug" disabled class="w-full px-4 py-2.5 bg-green-100 text-green-700 rounded-xl font-medium text-sm cursor-default">خطتك الحالية</button>
            </div>
        </template>
    </div>

    <!-- Invoices -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">سجل الفواتير</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">رقم الفاتورة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الخطة</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">المبلغ</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">التاريخ</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="inv in invoices" :key="inv.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono text-gray-800" x-text="'#' + inv.id"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="inv.plan_name"></td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800" x-text="inv.amount + ' د.ج'"></td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="inv.created_at"></td>
                            <td class="px-6 py-4"><span :class="inv.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="inv.status === 'paid' ? 'مدفوعة' : 'معلّقة'"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="invoices.length === 0" class="text-center py-8 text-gray-400 text-sm">لا توجد فواتير</div>
    </div>
</div>

@push('scripts')
<script>
function subscriptionManager() {
    return {
        current: {}, plans: [], invoices: [],
        async loadSubscription() {
            try { const r = await fetch('/api/v1/dashboard/subscription', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.current = d.current || {}; this.plans = d.plans || []; this.invoices = d.invoices || []; } catch(e) {}
        },
        async upgradePlan(plan) {
            if(!confirm(`هل تريد الترقية إلى خطة "${plan.name}" بسعر ${plan.price} د.ج/شهر؟`)) return;
            await fetch('/api/v1/dashboard/subscription/upgrade', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ plan_id: plan.id }) });
            await this.loadSubscription();
        }
    }
}
</script>
@endpush
@endsection
