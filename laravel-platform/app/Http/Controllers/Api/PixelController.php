<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StorePixel;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PixelController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront) - Get pixels for rendering
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get active pixels for a store (used by storefront to inject tracking scripts)
     * @route GET /api/v1/store/{store}/pixels
     */
    public function storePixels(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $pixels = StorePixel::where('store_id', $store->id)
            ->where('is_active', true)
            ->get();

        $scripts = $pixels->map(function ($pixel) {
            return [
                'platform' => $pixel->platform,
                'pixel_id' => $pixel->pixel_id,
                'script' => $pixel->generateScript(),
                'tracked_events' => $pixel->tracked_events ?? StorePixel::DEFAULT_EVENTS,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $scripts,
        ]);
    }

    /**
     * Track pixel event (server-side tracking)
     * @route POST /api/v1/store/{store}/pixels/track
     */
    public function trackEvent(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'event' => 'required|string|in:page_view,view_content,add_to_cart,initiate_checkout,purchase,search,lead',
            'data' => 'nullable|array',
            'data.value' => 'nullable|numeric',
            'data.currency' => 'nullable|string',
            'data.content_ids' => 'nullable|array',
            'data.content_name' => 'nullable|string',
            'data.content_category' => 'nullable|string',
            'data.num_items' => 'nullable|integer',
            'data.search_string' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get active pixels that track this event
        $pixels = StorePixel::where('store_id', $store->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn($pixel) => $pixel->shouldTrackEvent($request->event));

        $tracked = [];

        foreach ($pixels as $pixel) {
            // TODO: Implement server-side tracking via Conversions API
            // Facebook: Conversions API
            // TikTok: Events API
            // Google: Measurement Protocol

            $tracked[] = [
                'platform' => $pixel->platform,
                'event' => $request->event,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $tracked,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all pixels for the store
     * @route GET /api/v1/dashboard/pixels
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $pixels = StorePixel::where('store_id', $store->id)
            ->orderBy('platform')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'platform' => $pixel->platform,
                    'platform_name' => $pixel->platform_name,
                    'pixel_id' => $pixel->pixel_id,
                    'name' => $pixel->name,
                    'is_active' => $pixel->is_active,
                    'tracked_events' => $pixel->tracked_events ?? StorePixel::DEFAULT_EVENTS,
                    'has_access_token' => !empty($pixel->access_token),
                    'created_at' => $pixel->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pixels,
            'available_platforms' => StorePixel::PLATFORMS,
            'available_events' => StorePixel::DEFAULT_EVENTS,
        ]);
    }

    /**
     * Create or update a pixel
     * @route POST /api/v1/dashboard/pixels
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check feature access
        if (!$store->canUseFeature('custom_pixel')) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الميزة غير متوفرة في خطتك الحالية. يرجى الترقية.',
                'upgrade_required' => true,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|in:facebook,tiktok,snapchat,google_analytics,google_ads,twitter,pinterest,custom',
            'pixel_id' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'tracked_events' => 'nullable|array',
            'tracked_events.*' => 'string|in:page_view,view_content,add_to_cart,initiate_checkout,purchase,search,lead',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $pixel = StorePixel::updateOrCreate(
            [
                'store_id' => $store->id,
                'platform' => $request->platform,
                'pixel_id' => $request->pixel_id,
            ],
            [
                'name' => $request->name ?? StorePixel::PLATFORMS[$request->platform] ?? $request->platform,
                'access_token' => $request->access_token,
                'is_active' => $request->is_active ?? true,
                'tracked_events' => $request->tracked_events ?? StorePixel::DEFAULT_EVENTS,
                'settings' => $request->settings,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ البكسل بنجاح',
            'data' => [
                'id' => $pixel->id,
                'platform' => $pixel->platform,
                'pixel_id' => $pixel->pixel_id,
                'is_active' => $pixel->is_active,
            ],
        ]);
    }

    /**
     * Update pixel
     * @route PUT /api/v1/dashboard/pixels/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $pixel = StorePixel::where('store_id', $store->id)->where('id', $id)->first();

        if (!$pixel) {
            return response()->json(['success' => false, 'message' => 'البكسل غير موجود'], 404);
        }

        $pixel->update($request->only([
            'pixel_id', 'name', 'access_token', 'is_active', 'tracked_events', 'settings'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث البكسل',
            'data' => $pixel->fresh(),
        ]);
    }

    /**
     * Delete pixel
     * @route DELETE /api/v1/dashboard/pixels/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $pixel = StorePixel::where('store_id', $store->id)->where('id', $id)->first();

        if (!$pixel) {
            return response()->json(['success' => false, 'message' => 'البكسل غير موجود'], 404);
        }

        $pixel->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف البكسل',
        ]);
    }

    /**
     * Toggle pixel active status
     * @route PUT /api/v1/dashboard/pixels/{id}/toggle
     */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $pixel = StorePixel::where('store_id', $store->id)->where('id', $id)->first();

        if (!$pixel) {
            return response()->json(['success' => false, 'message' => 'البكسل غير موجود'], 404);
        }

        $pixel->update(['is_active' => !$pixel->is_active]);

        return response()->json([
            'success' => true,
            'message' => $pixel->is_active ? 'تم تفعيل البكسل' : 'تم إيقاف البكسل',
            'data' => ['is_active' => $pixel->is_active],
        ]);
    }
}
