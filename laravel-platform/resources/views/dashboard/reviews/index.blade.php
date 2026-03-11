@extends('layouts.dashboard')
@section('title', 'إدارة التقييمات')

@section('content')
<div x-data="reviewsManager()" x-init="loadReviews()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">التقييمات</h2>
            <p class="text-sm text-gray-500 mt-1">إدارة تقييمات العملاء والرد عليها</p>
        </div>
        <div class="flex items-center gap-2">
            <select x-model="filter" @change="loadReviews()" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none text-sm">
                <option value="">الكل</option>
                <option value="pending">بانتظار الموافقة</option>
                <option value="approved">معتمدة</option>
                <option value="rejected">مرفوضة</option>
            </select>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">إجمالي التقييمات</p>
            <p class="text-2xl font-black text-gray-800 mt-1" x-text="stats.total || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">متوسط التقييم</p>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-2xl font-black text-yellow-500" x-text="(stats.average || 0).toFixed(1)"></p>
                <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">بانتظار الموافقة</p>
            <p class="text-2xl font-black text-amber-600 mt-1" x-text="stats.pending || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">تقييمات 5 نجوم</p>
            <p class="text-2xl font-black text-green-600 mt-1" x-text="stats.five_star || 0"></p>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="space-y-4">
        <template x-for="review in reviews" :key="review.id">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 card-hover">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold" x-text="review.customer_name?.charAt(0) || '?'"></span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="font-bold text-gray-800" x-text="review.customer_name"></h4>
                                <div class="flex items-center gap-0.5">
                                    <template x-for="i in 5">
                                        <svg :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    </template>
                                </div>
                                <span class="text-xs text-gray-400" x-text="review.created_at"></span>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">المنتج: <span class="font-medium text-gray-700" x-text="review.product_name"></span></p>
                            <p class="text-gray-700" x-text="review.comment"></p>
                            <!-- Reply -->
                            <div x-show="review.reply" class="mt-3 bg-primary-50 rounded-xl p-3 border-r-4 border-primary-500">
                                <p class="text-xs font-bold text-primary-600 mb-1">ردّك:</p>
                                <p class="text-sm text-gray-700" x-text="review.reply"></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span :class="{
                            'bg-amber-100 text-amber-700': review.status === 'pending',
                            'bg-green-100 text-green-700': review.status === 'approved',
                            'bg-red-100 text-red-700': review.status === 'rejected'
                        }" class="text-xs font-bold px-2.5 py-1 rounded-full" x-text="review.status === 'pending' ? 'بانتظار' : review.status === 'approved' ? 'معتمد' : 'مرفوض'"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100">
                    <button x-show="review.status !== 'approved'" @click="updateStatus(review.id, 'approved')" class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-medium hover:bg-green-200">اعتماد</button>
                    <button x-show="review.status !== 'rejected'" @click="updateStatus(review.id, 'rejected')" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200">رفض</button>
                    <button @click="openReply(review)" class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg text-sm font-medium hover:bg-primary-200">
                        <span x-text="review.reply ? 'تعديل الرد' : 'الرد'"></span>
                    </button>
                    <button @click="deleteReview(review.id)" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 mr-auto">حذف</button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="reviews.length === 0 && !loading" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
        <h3 class="text-lg font-bold text-gray-600 mb-2">لا توجد تقييمات</h3>
        <p class="text-gray-500">ستظهر التقييمات هنا عندما يقيّم العملاء منتجاتك</p>
    </div>

    <!-- Reply Modal -->
    <div x-show="showReplyModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="showReplyModal = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-800">الرد على التقييم</h3></div>
            <form @submit.prevent="submitReply()" class="p-6 space-y-4">
                <textarea x-model="replyText" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 outline-none resize-none" placeholder="اكتب ردّك هنا..."></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium">إرسال الرد</button>
                    <button type="button" @click="showReplyModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reviewsManager() {
    return {
        reviews: [], loading: true, filter: '', showReplyModal: false, replyText: '', replyReviewId: null,
        stats: { total: 0, average: 0, pending: 0, five_star: 0 },
        async loadReviews() {
            try { const r = await fetch(`/api/store/reviews?status=${this.filter}`, { headers: { 'Accept': 'application/json' } }); const d = await r.json(); this.reviews = d.data || []; this.stats = d.stats || this.stats; } catch(e) {}
            this.loading = false;
        },
        async updateStatus(id, status) { await fetch(`/api/store/reviews/${id}/status`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ status }) }); await this.loadReviews(); },
        openReply(review) { this.replyReviewId = review.id; this.replyText = review.reply || ''; this.showReplyModal = true; },
        async submitReply() { await fetch(`/api/store/reviews/${this.replyReviewId}/reply`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ reply: this.replyText }) }); this.showReplyModal = false; await this.loadReviews(); },
        async deleteReview(id) { if(!confirm('حذف هذا التقييم؟')) return; await fetch(`/api/store/reviews/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); await this.loadReviews(); }
    }
}
</script>
@endpush
@endsection
