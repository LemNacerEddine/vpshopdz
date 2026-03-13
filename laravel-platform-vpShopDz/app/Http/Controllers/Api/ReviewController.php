<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get product reviews
     * @route GET /api/v1/store/{store}/products/{product}/reviews
     */
    public function productReviews(string $storeId, string $productId, Request $request): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $reviews = Review::where('store_id', $store->id)
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        // Calculate rating summary
        $ratingStats = Review::where('store_id', $store->id)
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as average, COUNT(*) as total,
                         SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                         SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                         SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                         SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                         SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews->items(),
                'summary' => [
                    'average' => round($ratingStats->average ?? 0, 1),
                    'total' => $ratingStats->total ?? 0,
                    'distribution' => [
                        5 => $ratingStats->five_star ?? 0,
                        4 => $ratingStats->four_star ?? 0,
                        3 => $ratingStats->three_star ?? 0,
                        2 => $ratingStats->two_star ?? 0,
                        1 => $ratingStats->one_star ?? 0,
                    ],
                ],
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'total' => $reviews->total(),
                ],
            ],
        ]);
    }

    /**
     * Submit review
     * @route POST /api/v1/store/{store}/products/{product}/reviews
     */
    public function submitReview(Request $request, string $storeId, string $productId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $product = Product::where('store_id', $store->id)->where('id', $productId)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Auto-approve setting
        $autoApprove = $store->settings['auto_approve_reviews'] ?? false;

        $review = Review::create([
            'store_id' => $store->id,
            'product_id' => $productId,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $autoApprove,
        ]);

        return response()->json([
            'success' => true,
            'message' => $autoApprove ? 'تم نشر تقييمك' : 'شكراً! سيتم مراجعة تقييمك قبل النشر.',
            'data' => $review,
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all reviews
     * @route GET /api/v1/dashboard/reviews
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Review::where('store_id', $store->id)->with('product:id,name,name_ar');

        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
                'pending_count' => Review::where('store_id', $store->id)->where('is_approved', false)->count(),
            ],
        ]);
    }

    /**
     * Approve/Reject review
     * @route PUT /api/v1/dashboard/reviews/{id}/approve
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $review = Review::where('store_id', $store->id)->where('id', $id)->first();

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'التقييم غير موجود'], 404);
        }

        $review->update([
            'is_approved' => !$review->is_approved,
        ]);

        return response()->json([
            'success' => true,
            'message' => $review->is_approved ? 'تم الموافقة على التقييم' : 'تم رفض التقييم',
        ]);
    }

    /**
     * Reply to review
     * @route PUT /api/v1/dashboard/reviews/{id}/reply
     */
    public function reply(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $review = Review::where('store_id', $store->id)->where('id', $id)->first();

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'التقييم غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reply' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $review->update([
            'reply' => $request->reply,
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الرد على التقييم',
        ]);
    }

    /**
     * Delete review
     * @route DELETE /api/v1/dashboard/reviews/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $review = Review::where('store_id', $store->id)->where('id', $id)->first();

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'التقييم غير موجود'], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم',
        ]);
    }
}
