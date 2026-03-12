@extends('layouts.dashboard')
@section('title', 'إعدادات المتجر')

@section('content')
<div x-data="settingsManager()" x-init="loadSettings()">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">إعدادات المتجر</h2>
        <p class="text-sm text-gray-500 mt-1">تخصيص إعدادات متجرك الأساسية</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6 overflow-x-auto">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">عام</button>
        <button @click="tab = 'contact'" :class="tab === 'contact' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">التواصل</button>
        <button @click="tab = 'checkout'" :class="tab === 'checkout' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">الطلب والدفع</button>
        <button @click="tab = 'seo'" :class="tab === 'seo' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">SEO</button>
        <button @click="tab = 'social'" :class="tab === 'social' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">التواصل الاجتماعي</button>
        <button @click="tab = 'whatsapp'" :class="tab === 'whatsapp' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">📱 واتساب</button>
        <button @click="tab = 'legal'" :class="tab === 'legal' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap">قانوني</button>
    </div>

    <form @submit.prevent="saveSettings()">
        <!-- General -->
        <div x-show="tab === 'general'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم المتجر بالعربية *</label>
                    <input type="text" x-model="settings.name_ar" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم المتجر بالفرنسية</label>
                    <input type="text" x-model="settings.name_fr" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف المتجر</label>
                <textarea x-model="settings.description" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none"></textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العملة</label>
                    <select x-model="settings.currency" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="DZD">دينار جزائري (د.ج)</option>
                        <option value="EUR">يورو (€)</option>
                        <option value="USD">دولار ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اللغة الافتراضية</label>
                    <select x-model="settings.default_language" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="ar">العربية</option>
                        <option value="fr">الفرنسية</option>
                        <option value="en">الإنجليزية</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">شعار المتجر</label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                        <img x-show="settings.logo" :src="settings.logo" class="w-full h-full object-contain">
                        <svg x-show="!settings.logo" class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <label class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium text-sm cursor-pointer">
                        تغيير الشعار
                        <input type="file" accept="image/*" @change="uploadLogo($event)" class="hidden">
                    </label>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div x-show="tab === 'contact'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" x-model="settings.email" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" x-model="settings.phone" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                <input type="text" x-model="settings.address" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم WhatsApp (للزر العائم)</label>
                <input type="text" x-model="settings.whatsapp" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="213XXXXXXXXX">
            </div>
        </div>

        <!-- Checkout -->
        <div x-show="tab === 'checkout'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">طرق الدفع</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.cod_enabled" class="w-4 h-4 text-primary-600 rounded">
                        <span class="text-sm text-gray-700">الدفع عند الاستلام (COD)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.cib_enabled" class="w-4 h-4 text-primary-600 rounded">
                        <span class="text-sm text-gray-700">بطاقة CIB</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.edahabia_enabled" class="w-4 h-4 text-primary-600 rounded">
                        <span class="text-sm text-gray-700">بطاقة الذهبية (Edahabia)</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأدنى للطلب (د.ج)</label>
                <input type="number" x-model="settings.min_order_amount" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="0">
            </div>
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" x-model="settings.guest_checkout" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div></label>
                <span class="text-sm font-medium text-gray-700">السماح بالطلب بدون تسجيل</span>
            </div>
        </div>

        <!-- SEO -->
        <div x-show="tab === 'seo'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">عنوان الصفحة (Meta Title)</label>
                <input type="text" x-model="settings.meta_title" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف الصفحة (Meta Description)</label>
                <textarea x-model="settings.meta_description" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">كلمات مفتاحية</label>
                <input type="text" x-model="settings.meta_keywords" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="كلمة1, كلمة2, كلمة3">
            </div>
        </div>

        <!-- Social -->
        <div x-show="tab === 'social'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                <input type="url" x-model="settings.facebook" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="https://facebook.com/...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                <input type="url" x-model="settings.instagram" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="https://instagram.com/...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TikTok</label>
                <input type="url" x-model="settings.tiktok" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="https://tiktok.com/@...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                <input type="url" x-model="settings.youtube" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="https://youtube.com/...">
            </div>
        </div>

        <!-- WhatsApp -->
        <div x-show="tab === 'whatsapp'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                <p class="text-sm text-green-700 font-medium">إعدادات واتساب للتواصل مع العملاء وإرسال إشعارات السلة المتروكة</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.whatsapp_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </label>
                <span class="text-sm font-medium text-gray-700">تفعيل إشعارات واتساب</span>
            </div>
            <div x-show="settings.whatsapp_enabled" x-transition class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الاتصال</label>
                    <select x-model="settings.whatsapp_mode" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="green_api">Green API (مجاني)</option>
                        <option value="business_api">Meta Business API</option>
                    </select>
                </div>
                <div x-show="settings.whatsapp_mode === 'green_api'" class="space-y-4 bg-gray-50 p-4 rounded-xl">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instance ID</label>
                            <input type="text" x-model="settings.green_api_instance_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="1101XXXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Token</label>
                            <input type="text" x-model="settings.green_api_token" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                        </div>
                    </div>
                </div>
                <div x-show="settings.whatsapp_mode === 'business_api'" class="space-y-4 bg-gray-50 p-4 rounded-xl">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                        <input type="text" x-model="settings.whatsapp_phone_number_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                        <input type="text" x-model="settings.whatsapp_access_token" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم واتساب المتجر</label>
                    <input type="text" x-model="settings.whatsapp" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="213XXXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نموذج رسالة السلة المتروكة</label>
                    <textarea x-model="settings.whatsapp_message_ar" rows="4" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none" placeholder="مرحبا {name}، لديك سلة بقيمة {total} د.ج..."></textarea>
                    <p class="text-xs text-gray-400 mt-1">متغيرات: {name} {total} {link} {checkout_id} {items_count}</p>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">إرسال بعد (دقائق)</label>
                        <input type="number" x-model="settings.whatsapp_delay_minutes" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">حد الإرسال اليومي</label>
                        <input type="number" x-model="settings.whatsapp_max_per_run" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="10">
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal -->
        <div x-show="tab === 'legal'" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم السجل التجاري (RC)</label>
                    <input type="text" x-model="settings.store_rc" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                    <input type="text" x-model="settings.store_nif" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                    <input type="text" x-model="settings.store_nis" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم المادة (AI)</label>
                    <input type="text" x-model="settings.store_ai" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-6 flex items-center justify-between">
            <div>
                <p x-show="saveMsg" :class="saveOk ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium flex items-center gap-1.5">
                    <svg x-show="saveOk" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="saveMsg"></span>
                </p>
            </div>
            <button type="submit" :disabled="saving" class="px-8 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30 disabled:opacity-50 flex items-center gap-2">
                <svg x-show="saving" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                حفظ الإعدادات
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function settingsManager() {
    return {
        tab: 'general', saving: false,
        saveMsg: '', saveOk: true,
        settings: {
            name_ar: '', name_fr: '', description: '', currency: 'DZD', default_language: 'ar', logo: '',
            email: '', phone: '', address: '', whatsapp: '',
            cod_enabled: true, cib_enabled: false, edahabia_enabled: false,
            min_order_amount: 0, guest_checkout: true,
            meta_title: '', meta_description: '', meta_keywords: '',
            facebook: '', instagram: '', tiktok: '', youtube: '',
            whatsapp_enabled: false, whatsapp_mode: 'green_api',
            green_api_instance_id: '', green_api_token: '',
            whatsapp_phone_number_id: '', whatsapp_access_token: '',
            whatsapp_message_ar: '', whatsapp_delay_minutes: 30, whatsapp_max_per_run: 10,
            store_rc: '', store_nif: '', store_nis: '', store_ai: ''
        },
        async loadSettings() {
            try {
                const r = await fetch('/api/v1/dashboard/store/settings', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const d = await r.json();
                this.settings = {...this.settings, ...(d.data || d)};
            } catch(e) {}
        },
        async saveSettings() {
            this.saving = true;
            this.saveMsg = '';
            try {
                const r = await fetch('/api/v1/dashboard/store/settings', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.settings)
                });
                const d = await r.json();
                if (r.ok) {
                    this.saveOk = true;
                    this.saveMsg = 'تم حفظ الإعدادات بنجاح ✓';
                } else {
                    this.saveOk = false;
                    this.saveMsg = d.message || 'حدث خطأ أثناء الحفظ';
                }
            } catch(e) {
                this.saveOk = false;
                this.saveMsg = 'خطأ في الاتصال بالخادم';
            }
            this.saving = false;
            setTimeout(() => this.saveMsg = '', 4000);
        },
        async uploadLogo(event) {
            const file = event.target.files[0]; if (!file) return;
            const fd = new FormData(); fd.append('file', file); fd.append('type', 'logo');
            try {
                const r = await fetch('/api/v1/dashboard/media/upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
                const d = await r.json();
                if (d.url || d.data?.url) this.settings.logo = d.url || d.data.url;
            } catch(e) {}
        }
    }
}
</script>
@endpush
@endsection
