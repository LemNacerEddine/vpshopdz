<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacebookAd;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacebookAdController extends Controller
{
    /**
     * Get all Facebook ads for the store
     * @route GET /api/v1/dashboard/facebook-ads
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check feature access
        if (!$store->canUseFeature('facebook_ads')) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الميزة غير متوفرة في خطتك الحالية',
                'upgrade_required' => true,
            ], 403);
        }

        $query = FacebookAd::where('store_id', $store->id)->with('product');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $ads = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'meta' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'total' => $ads->total(),
            ],
        ]);
    }

    /**
     * Create a new Facebook ad
     * @route POST /api/v1/dashboard/facebook-ads
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'daily_budget' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|min:1|max:90',
            'target_country' => 'nullable|string|max:2',
            'target_age_min' => 'nullable|integer|min:13|max:65',
            'target_age_max' => 'nullable|integer|min:13|max:65',
            'target_interests' => 'nullable|array',
            'ad_text' => 'required|string|max:2000',
            'ad_headline' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify product belongs to store
        $product = Product::where('id', $request->product_id)
            ->where('store_id', $store->id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير موجود في متجرك',
            ], 404);
        }

        $ad = FacebookAd::create([
            'store_id' => $store->id,
            'product_id' => $request->product_id,
            'status' => 'draft',
            'daily_budget_cents' => $request->daily_budget * 100,
            'duration_days' => $request->duration_days,
            'target_country' => $request->target_country ?? 'DZ',
            'target_age_min' => $request->target_age_min ?? 18,
            'target_age_max' => $request->target_age_max ?? 55,
            'target_interests' => $request->target_interests ?? [],
            'ad_text' => $request->ad_text,
            'ad_headline' => $request->ad_headline,
            'landing_url' => $store->getUrl() . '/product/' . $product->slug,
            'starts_at' => now(),
            'ends_at' => now()->addDays($request->duration_days),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعلان كمسودة',
            'data' => $ad,
        ], 201);
    }

    /**
     * Get ad details
     * @route GET /api/v1/dashboard/facebook-ads/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $ad = FacebookAd::where('store_id', $store->id)
            ->where('id', $id)
            ->with('product')
            ->first();

        if (!$ad) {
            return response()->json(['success' => false, 'message' => 'الإعلان غير موجود'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($ad->toArray(), [
                'daily_budget' => $ad->daily_budget,
                'spend' => $ad->spend,
                'ctr' => $ad->ctr,
                'cpc' => $ad->cpc,
                'cpm' => $ad->cpm,
                'is_running' => $ad->isRunning(),
                'status_label' => $ad->status_label,
            ]),
        ]);
    }

    /**
     * Update ad
     * @route PUT /api/v1/dashboard/facebook-ads/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $ad = FacebookAd::where('store_id', $store->id)->where('id', $id)->first();

        if (!$ad) {
            return response()->json(['success' => false, 'message' => 'الإعلان غير موجود'], 404);
        }

        if (!in_array($ad->status, ['draft', 'paused'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل إعلان نشط',
            ], 400);
        }

        $updateData = $request->only([
            'ad_text', 'ad_headline', 'target_age_min', 'target_age_max', 'target_interests'
        ]);

        if ($request->has('daily_budget')) {
            $updateData['daily_budget_cents'] = $request->daily_budget * 100;
        }

        $ad->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعلان',
            'data' => $ad->fresh(),
        ]);
    }

    /**
     * Pause/Resume ad
     * @route PUT /api/v1/dashboard/facebook-ads/{id}/toggle
     */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $ad = FacebookAd::where('store_id', $store->id)->where('id', $id)->first();

        if (!$ad) {
            return response()->json(['success' => false, 'message' => 'الإعلان غير موجود'], 404);
        }

        $newStatus = match($ad->status) {
            'active' => 'paused',
            'paused' => 'active',
            'draft' => 'pending',
            default => $ad->status,
        };

        $ad->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "تم تغيير حالة الإعلان إلى: {$ad->status_label}",
            'data' => ['status' => $newStatus],
        ]);
    }

    /**
     * Delete ad
     * @route DELETE /api/v1/dashboard/facebook-ads/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $ad = FacebookAd::where('store_id', $store->id)->where('id', $id)->first();

        if (!$ad) {
            return response()->json(['success' => false, 'message' => 'الإعلان غير موجود'], 404);
        }

        if ($ad->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف إعلان نشط. أوقفه أولاً.',
            ], 400);
        }

        $ad->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإعلان',
        ]);
    }

    /**
     * Get ad statistics summary
     * @route GET /api/v1/dashboard/facebook-ads/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $stats = [
            'total_ads' => FacebookAd::where('store_id', $store->id)->count(),
            'active_ads' => FacebookAd::where('store_id', $store->id)->where('status', 'active')->count(),
            'total_spend' => FacebookAd::where('store_id', $store->id)->sum('spend_cents') / 100,
            'total_impressions' => FacebookAd::where('store_id', $store->id)->sum('impressions'),
            'total_clicks' => FacebookAd::where('store_id', $store->id)->sum('clicks'),
            'total_reach' => FacebookAd::where('store_id', $store->id)->sum('reach'),
            'avg_ctr' => 0,
            'avg_cpc' => 0,
        ];

        if ($stats['total_impressions'] > 0) {
            $stats['avg_ctr'] = round(($stats['total_clicks'] / $stats['total_impressions']) * 100, 2);
        }
        if ($stats['total_clicks'] > 0) {
            $stats['avg_cpc'] = round($stats['total_spend'] / $stats['total_clicks'], 2);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
