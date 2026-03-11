@extends('layouts.dashboard')
@section('title', 'مكتبة الوسائط')

@section('content')
<div x-data="mediaManager()" x-init="loadMedia()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">مكتبة الوسائط</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة صور ومرفقات متجرك</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500" x-text="storageUsed + ' / ' + storageLimit"></span>
            <label class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                رفع ملفات
                <input type="file" multiple accept="image/*" @change="uploadFiles($event)" class="hidden">
            </label>
        </div>
    </div>

    <!-- Upload Progress -->
    <div x-show="uploading" class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-5 h-5 text-primary-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-sm font-medium text-gray-700">جاري الرفع... <span x-text="uploadProgress + '%'"></span></span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full transition-all" :style="'width:' + uploadProgress + '%'"></div>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="flex items-center gap-2 mb-4">
        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg></button>
        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'" class="p-2 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg></button>
        <div class="mr-auto text-sm text-gray-500" x-text="media.length + ' ملف'"></div>
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <template x-for="file in media" :key="file.id">
            <div class="group relative bg-white rounded-xl border border-gray-100 overflow-hidden card-hover cursor-pointer" @click="selectFile(file)">
                <div class="aspect-square bg-gray-100">
                    <img :src="file.thumbnail || file.url" :alt="file.name" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="p-2">
                    <p class="text-xs text-gray-700 truncate" x-text="file.name"></p>
                    <p class="text-xs text-gray-400" x-text="file.size_human"></p>
                </div>
                <!-- Hover Actions -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                    <button @click.stop="copyUrl(file)" class="p-1.5 bg-white/90 rounded-lg shadow-sm hover:bg-white text-gray-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg></button>
                    <button @click.stop="deleteFile(file.id)" class="p-1.5 bg-white/90 rounded-lg shadow-sm hover:bg-red-50 text-gray-600 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                </div>
            </div>
        </template>
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-right px-6 py-3 text-xs font-bold text-gray-500">الملف</th>
                    <th class="text-right px-6 py-3 text-xs font-bold text-gray-500">الحجم</th>
                    <th class="text-right px-6 py-3 text-xs font-bold text-gray-500">التاريخ</th>
                    <th class="text-right px-6 py-3 text-xs font-bold text-gray-500">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <template x-for="file in media" :key="file.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="file.thumbnail || file.url" class="w-10 h-10 rounded-lg object-cover">
                                <span class="text-sm text-gray-800 truncate max-w-xs" x-text="file.name"></span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500" x-text="file.size_human"></td>
                        <td class="px-6 py-3 text-sm text-gray-500" x-text="file.created_at"></td>
                        <td class="px-6 py-3">
                            <div class="flex gap-1">
                                <button @click="copyUrl(file)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500">نسخ</button>
                                <button @click="deleteFile(file.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600">حذف</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="media.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">مكتبة الوسائط فارغة</h3>
        <p class="text-gray-500 mb-4">ارفع صور منتجاتك وملفاتك هنا</p>
    </div>
</div>

@push('scripts')
<script>
function mediaManager() {
    return {
        media: [], loading: true, viewMode: 'grid', uploading: false, uploadProgress: 0,
        storageUsed: '0 MB', storageLimit: '500 MB',
        async loadMedia() { try { const r = await fetch('/api/store/media', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.media = d.data || []; this.storageUsed = d.storage_used || '0 MB'; this.storageLimit = d.storage_limit || '500 MB'; } catch(e) {} this.loading = false; },
        async uploadFiles(event) {
            const files = event.target.files; if (!files.length) return;
            this.uploading = true; this.uploadProgress = 0;
            const formData = new FormData();
            for (let f of files) formData.append('files[]', f);
            const xhr = new XMLHttpRequest();
            xhr.upload.onprogress = (e) => { if (e.lengthComputable) this.uploadProgress = Math.round((e.loaded / e.total) * 100); };
            xhr.onload = () => { this.uploading = false; this.loadMedia(); };
            xhr.onerror = () => { this.uploading = false; alert('فشل الرفع'); };
            xhr.open('POST', '/api/store/media/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(formData);
        },
        copyUrl(file) { navigator.clipboard.writeText(file.url); },
        selectFile(file) { /* TODO: preview modal */ },
        async deleteFile(id) { if(!confirm('حذف هذا الملف؟')) return; await fetch(`/api/store/media/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadMedia(); }
    }
}
</script>
@endpush
@endsection
