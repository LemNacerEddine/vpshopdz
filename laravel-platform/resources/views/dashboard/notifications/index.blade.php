@extends('layouts.dashboard')
@section('title', 'الإشعارات')

@section('content')
<div x-data="notificationsManager()" x-init="loadSettings()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">إعدادات الإشعارات</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة إشعارات WhatsApp و Telegram</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- WhatsApp -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">WhatsApp</h3>
                    <p class="text-xs text-gray-500">إشعارات فورية عبر واتساب</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mr-auto">
                    <input type="checkbox" x-model="settings.whatsapp_enabled" @change="saveSettings()" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
            </div>
            <div class="p-5 space-y-4" x-show="settings.whatsapp_enabled">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم WhatsApp</label>
                    <input type="text" x-model="settings.whatsapp_number" @change="saveSettings()" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" placeholder="213XXXXXXXXX" dir="ltr">
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">إرسال إشعار عند:</p>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.whatsapp_on_new_order" @change="saveSettings()" class="w-4 h-4 text-green-600 rounded">
                        <span class="text-sm text-gray-700">طلب جديد</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.whatsapp_on_status_change" @change="saveSettings()" class="w-4 h-4 text-green-600 rounded">
                        <span class="text-sm text-gray-700">تغيير حالة الطلب</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.whatsapp_on_abandoned" @change="saveSettings()" class="w-4 h-4 text-green-600 rounded">
                        <span class="text-sm text-gray-700">سلة متروكة</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Telegram -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Telegram</h3>
                    <p class="text-xs text-gray-500">إشعارات عبر بوت تيليجرام</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mr-auto">
                    <input type="checkbox" x-model="settings.telegram_enabled" @change="saveSettings()" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <div class="p-5 space-y-4" x-show="settings.telegram_enabled">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bot Token</label>
                    <input type="text" x-model="settings.telegram_bot_token" @change="saveSettings()" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" placeholder="123456:ABC-DEF..." dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chat ID</label>
                    <input type="text" x-model="settings.telegram_chat_id" @change="saveSettings()" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" placeholder="123456789" dir="ltr">
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">إرسال إشعار عند:</p>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.telegram_on_new_order" @change="saveSettings()" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">طلب جديد</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" x-model="settings.telegram_on_low_stock" @change="saveSettings()" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">مخزون منخفض</span>
                    </label>
                </div>
                <button @click="testTelegram()" class="w-full px-4 py-2.5 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 font-medium text-sm">إرسال رسالة تجريبية</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function notificationsManager() {
    return {
        settings: {
            whatsapp_enabled: false, whatsapp_number: '', whatsapp_on_new_order: true, whatsapp_on_status_change: false, whatsapp_on_abandoned: false,
            telegram_enabled: false, telegram_bot_token: '', telegram_chat_id: '', telegram_on_new_order: true, telegram_on_low_stock: false
        },
        async loadSettings() {
            try { const r = await fetch('/api/store/notifications/settings', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.settings = {...this.settings, ...d}; } catch(e) {}
        },
        async saveSettings() {
            await fetch('/api/store/notifications/settings', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.settings) });
        },
        async testTelegram() {
            const r = await fetch('/api/store/notifications/test-telegram', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            const d = await r.json();
            alert(d.success ? 'تم إرسال الرسالة التجريبية بنجاح!' : 'فشل الإرسال: ' + (d.message || 'خطأ'));
        }
    }
}
</script>
@endpush
@endsection
