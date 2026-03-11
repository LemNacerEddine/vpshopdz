@extends('layouts.dashboard')
@section('title', 'فريق العمل')

@section('content')
<div x-data="staffManager()" x-init="loadStaff()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">فريق العمل</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة أعضاء فريقك وصلاحياتهم</p>
        </div>
        <button @click="showModal = true; resetForm()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            دعوة عضو
        </button>
    </div>

    <!-- Members -->
    <div class="space-y-3">
        <template x-for="member in members" :key="member.id">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg" x-text="member.name?.charAt(0) || '?'"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="member.name"></h3>
                        <p class="text-sm text-gray-500" x-text="member.email"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="{
                        'bg-purple-100 text-purple-700': member.role === 'owner',
                        'bg-blue-100 text-blue-700': member.role === 'admin',
                        'bg-green-100 text-green-700': member.role === 'editor',
                        'bg-gray-100 text-gray-700': member.role === 'viewer'
                    }" class="text-xs font-bold px-3 py-1 rounded-full" x-text="getRoleName(member.role)"></span>
                    <button x-show="member.role !== 'owner'" @click="editMember(member)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <button x-show="member.role !== 'owner'" @click="removeMember(member.id)" class="p-2 hover:bg-red-50 rounded-lg text-gray-500 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                </div>
            </div>
        </template>
    </div>

    <!-- Pending Invitations -->
    <div x-show="invitations.length > 0" class="mt-8">
        <h3 class="font-bold text-gray-800 mb-4">دعوات معلّقة</h3>
        <div class="space-y-3">
            <template x-for="inv in invitations" :key="inv.id">
                <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800" x-text="inv.email"></p>
                            <p class="text-sm text-amber-600">بانتظار القبول</p>
                        </div>
                    </div>
                    <button @click="cancelInvitation(inv.id)" class="px-3 py-1.5 bg-amber-200 text-amber-800 rounded-lg text-sm font-medium hover:bg-amber-300">إلغاء الدعوة</button>
                </div>
            </template>
        </div>
    </div>

    <!-- Invite Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800">دعوة عضو جديد</h3></div>
            <form @submit.prevent="sendInvitation()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني *</label>
                    <input type="email" x-model="form.email" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الصلاحية *</label>
                    <select x-model="form.role" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="admin">مدير (كامل الصلاحيات)</option>
                        <option value="editor">محرر (إدارة المنتجات والطلبات)</option>
                        <option value="viewer">مشاهد (عرض فقط)</option>
                    </select>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">الصلاحيات:</h4>
                    <div x-show="form.role === 'admin'" class="text-sm text-gray-600 space-y-1">
                        <p>✅ إدارة المنتجات والطلبات والعملاء</p>
                        <p>✅ إدارة الإعدادات والثيمات</p>
                        <p>✅ عرض التحليلات والتقارير</p>
                    </div>
                    <div x-show="form.role === 'editor'" class="text-sm text-gray-600 space-y-1">
                        <p>✅ إدارة المنتجات والطلبات</p>
                        <p>❌ تغيير الإعدادات</p>
                        <p>✅ عرض التحليلات</p>
                    </div>
                    <div x-show="form.role === 'viewer'" class="text-sm text-gray-600 space-y-1">
                        <p>❌ لا يمكنه التعديل</p>
                        <p>✅ عرض المنتجات والطلبات</p>
                        <p>✅ عرض التحليلات</p>
                    </div>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">إرسال الدعوة</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function staffManager() {
    return {
        members: [], invitations: [], showModal: false,
        form: { email: '', role: 'editor' },
        resetForm() { this.form = { email: '', role: 'editor' }; },
        getRoleName(role) { return { owner: 'مالك', admin: 'مدير', editor: 'محرر', viewer: 'مشاهد' }[role] || role; },
        async loadStaff() { try { const r = await fetch('/api/store/staff', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.members = d.members || []; this.invitations = d.invitations || []; } catch(e) {} },
        async sendInvitation() { await fetch('/api/store/staff/invite', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) }); this.showModal = false; this.resetForm(); await this.loadStaff(); },
        editMember(m) { /* TODO: edit role modal */ },
        async removeMember(id) { if(!confirm('إزالة هذا العضو؟')) return; await fetch(`/api/store/staff/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadStaff(); },
        async cancelInvitation(id) { await fetch(`/api/store/staff/invitations/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadStaff(); }
    }
}
</script>
@endpush
@endsection
