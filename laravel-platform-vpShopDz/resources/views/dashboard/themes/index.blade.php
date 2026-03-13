@extends('layouts.dashboard')
@section('title', 'إدارة الثيمات')

@section('content')
<div x-data="themesManager()" x-init="loadThemes()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">الثيمات</h2>
            <p class="text-sm text-gray-500 mt-1">اختر وخصّص مظهر متجرك</p>
        </div>
    </div>

    <!-- Current Theme -->
    <div class="bg-gradient-to-l from-primary-600 to-primary-700 rounded-2xl p-6 text-white mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-white/70 text-sm mb-1">الثيم الحالي</p>
                <h3 class="text-2xl font-black" x-text="currentTheme?.name || 'لم يتم اختيار ثيم'"></h3>
                <p class="text-white/80 mt-1" x-text="currentTheme?.description || ''"></p>
            </div>
            <button @click="showCustomizer = true" class="bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-xl hover:bg-white/30 transition-all font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                تخصيص الألوان
            </button>
        </div>
    </div>

    <!-- Themes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="theme in themes" :key="theme.id">
            <div class="bg-white rounded-2xl border-2 overflow-hidden card-hover" :class="theme.id === currentTheme?.id ? 'border-primary-500 shadow-lg shadow-primary-500/20' : 'border-gray-100'">
                <!-- Theme Preview -->
                <div class="h-48 relative overflow-hidden" :style="'background: linear-gradient(135deg, ' + (theme.colors?.primary || '#10b981') + ' 0%, ' + (theme.colors?.secondary || '#059669') + ' 100%)'">
                    <div class="absolute inset-0 flex flex-col">
                        <!-- Mini Header -->
                        <div class="h-8 bg-white/10 backdrop-blur-sm flex items-center px-3 gap-2">
                            <div class="w-4 h-4 bg-white/30 rounded"></div>
                            <div class="w-16 h-2 bg-white/30 rounded-full"></div>
                            <div class="mr-auto flex gap-1"><div class="w-8 h-2 bg-white/20 rounded-full"></div><div class="w-8 h-2 bg-white/20 rounded-full"></div></div>
                        </div>
                        <!-- Mini Hero -->
                        <div class="flex-1 flex items-center justify-center p-4">
                            <div class="text-center">
                                <div class="w-32 h-3 bg-white/40 rounded-full mx-auto mb-2"></div>
                                <div class="w-24 h-2 bg-white/20 rounded-full mx-auto mb-3"></div>
                                <div class="w-16 h-5 bg-white/30 rounded-lg mx-auto"></div>
                            </div>
                        </div>
                        <!-- Mini Products -->
                        <div class="bg-white/10 backdrop-blur-sm p-3 flex gap-2">
                            <div class="flex-1 h-12 bg-white/20 rounded-lg"></div>
                            <div class="flex-1 h-12 bg-white/20 rounded-lg"></div>
                            <div class="flex-1 h-12 bg-white/20 rounded-lg"></div>
                        </div>
                    </div>
                    <!-- Active Badge -->
                    <div x-show="theme.id === currentTheme?.id" class="absolute top-3 right-3 bg-white text-primary-600 text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        مفعّل
                    </div>
                    <!-- Premium Badge -->
                    <div x-show="theme.is_premium" class="absolute top-3 left-3 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        Premium
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800" x-text="theme.name"></h3>
                        <span class="text-xs text-gray-500" x-text="theme.category || 'عام'"></span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4" x-text="theme.description"></p>
                    <!-- Color Palette Preview -->
                    <div class="flex items-center gap-1 mb-4">
                        <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="'background:' + (theme.colors?.primary || '#10b981')"></div>
                        <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="'background:' + (theme.colors?.secondary || '#059669')"></div>
                        <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="'background:' + (theme.colors?.accent || '#f59e0b')"></div>
                        <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="'background:' + (theme.colors?.background || '#ffffff')"></div>
                    </div>
                    <div class="flex gap-2">
                        <button x-show="theme.id !== currentTheme?.id" @click="activateTheme(theme.id)" class="flex-1 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium text-sm">تفعيل</button>
                        <button x-show="theme.id === currentTheme?.id" class="flex-1 px-4 py-2.5 bg-primary-100 text-primary-700 rounded-xl font-medium text-sm cursor-default">مفعّل حالياً</button>
                        <button @click="previewTheme(theme)" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium text-sm">معاينة</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Color Customizer Modal -->
    <div x-show="showCustomizer" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showCustomizer = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">تخصيص ألوان الثيم</h3>
                <p class="text-sm text-gray-500 mt-1">عدّل الألوان لتتناسب مع هوية متجرك</p>
            </div>
            <form @submit.prevent="saveCustomColors()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللون الرئيسي</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="customColors.primary" class="w-10 h-10 rounded-lg border-0 cursor-pointer">
                            <input type="text" x-model="customColors.primary" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللون الثانوي</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="customColors.secondary" class="w-10 h-10 rounded-lg border-0 cursor-pointer">
                            <input type="text" x-model="customColors.secondary" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">لون التمييز</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="customColors.accent" class="w-10 h-10 rounded-lg border-0 cursor-pointer">
                            <input type="text" x-model="customColors.accent" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">لون الخلفية</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="customColors.background" class="w-10 h-10 rounded-lg border-0 cursor-pointer">
                            <input type="text" x-model="customColors.background" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        </div>
                    </div>
                </div>
                <!-- Preview -->
                <div class="rounded-xl p-4 border border-gray-200" :style="'background:' + customColors.background">
                    <div class="h-8 rounded-lg mb-2" :style="'background:' + customColors.primary"></div>
                    <div class="flex gap-2">
                        <div class="flex-1 h-6 rounded" :style="'background:' + customColors.secondary"></div>
                        <div class="flex-1 h-6 rounded" :style="'background:' + customColors.accent"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">حفظ الألوان</button>
                    <button type="button" @click="showCustomizer = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function themesManager() {
    return {
        themes: [], currentTheme: null, showCustomizer: false,
        customColors: { primary: '#10b981', secondary: '#059669', accent: '#f59e0b', background: '#ffffff' },
        async loadThemes() {
            try {
                const r = await fetch('/api/v1/dashboard/themes', { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                this.themes = d.themes || [];
                this.currentTheme = d.current || null;
                if (this.currentTheme?.custom_colors) this.customColors = {...this.customColors, ...this.currentTheme.custom_colors};
            } catch(e) {}
        },
        async activateTheme(id) {
            try {
                await fetch(`/api/v1/dashboard/themes/${id}/activate`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                await this.loadThemes();
            } catch(e) {}
        },
        previewTheme(theme) { window.open(`/store-preview?theme=${theme.slug}`, '_blank'); },
        async saveCustomColors() {
            try {
                await fetch('/api/v1/dashboard/themes/active', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ colors: this.customColors }) });
                this.showCustomizer = false;
                await this.loadThemes();
            } catch(e) {}
        }
    }
}
</script>
@endpush
@endsection
