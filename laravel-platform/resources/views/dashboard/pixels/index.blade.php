@extends('layouts.dashboard')
@section('title', 'البكسلات')

@section('content')
<div x-data="pixelsManager()" x-init="loadPixels()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">بكسلات التتبع</h2>
            <p class="text-sm text-gray-500 mt-1">ربط متجرك بمنصات الإعلانات والتحليلات</p>
        </div>
        <button @click="showModal = true; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            إضافة بكسل
        </button>
    </div>

    <!-- Pixels Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="pixel in pixels" :key="pixel.id">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 card-hover">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="{
                        'bg-blue-100': pixel.type === 'facebook',
                        'bg-yellow-100': pixel.type === 'google_analytics',
                        'bg-red-100': pixel.type === 'google_ads',
                        'bg-gray-900': pixel.type === 'tiktok',
                        'bg-yellow-50': pixel.type === 'snapchat'
                    }">
                        <span class="text-lg font-bold" :class="{
                            'text-blue-600': pixel.type === 'facebook',
                            'text-yellow-600': pixel.type === 'google_analytics',
                            'text-red-600': pixel.type === 'google_ads',
                            'text-white': pixel.type === 'tiktok',
                            'text-yellow-500': pixel.type === 'snapchat'
                        }" x-text="pixel.type === 'facebook' ? 'f' : pixel.type === 'google_analytics' ? 'GA' : pixel.type === 'google_ads' ? 'G' : pixel.type === 'tiktok' ? 'T' : 'S'"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="pixel.name || getTypeName(pixel.type)"></h3>
                        <p class="text-xs text-gray-500 font-mono" x-text="pixel.pixel_id"></p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span :class="pixel.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="pixel.is_active ? 'مفعّل' : 'معطّل'"></span>
                    <div class="flex gap-1">
                        <button @click="togglePixel(pixel)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500" :title="pixel.is_active ? 'تعطيل' : 'تفعيل'">
                            <svg x-show="pixel.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            <svg x-show="!pixel.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                        <button @click="deletePixel(pixel.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div x-show="pixels.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد بكسلات</h3>
        <p class="text-gray-500 mb-4">أضف بكسلات لتتبع أداء إعلاناتك</p>
    </div>

    <!-- Add Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800">إضافة بكسل جديد</h3></div>
            <form @submit.prevent="savePixel()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المنصة *</label>
                    <select x-model="form.type" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="">اختر المنصة</option>
                        <option value="facebook">Facebook Pixel</option>
                        <option value="google_analytics">Google Analytics</option>
                        <option value="google_ads">Google Ads</option>
                        <option value="tiktok">TikTok Pixel</option>
                        <option value="snapchat">Snapchat Pixel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">معرّف البكسل *</label>
                    <input type="text" x-model="form.pixel_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr" placeholder="123456789">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم مخصص</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" placeholder="بكسل الحملة الرئيسية">
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">إضافة البكسل</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pixelsManager() {
    return {
        pixels: [], loading: true, showModal: false,
        form: { type: '', pixel_id: '', name: '' },
        resetForm() { this.form = { type: '', pixel_id: '', name: '' }; },
        getTypeName(type) { return { facebook: 'Facebook Pixel', google_analytics: 'Google Analytics', google_ads: 'Google Ads', tiktok: 'TikTok Pixel', snapchat: 'Snapchat Pixel' }[type] || type; },
        async loadPixels() { try { const r = await fetch('/api/store/pixels', { headers: { 'Accept': 'application/json' } }); this.pixels = (await r.json()).data || []; } catch(e) {} this.loading = false; },
        async savePixel() { await fetch('/api/store/pixels', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) }); this.showModal = false; this.resetForm(); await this.loadPixels(); },
        async togglePixel(pixel) { await fetch(`/api/store/pixels/${pixel.id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadPixels(); },
        async deletePixel(id) { if(!confirm('حذف هذا البكسل؟')) return; await fetch(`/api/store/pixels/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadPixels(); }
    }
}
</script>
@endpush
@endsection
