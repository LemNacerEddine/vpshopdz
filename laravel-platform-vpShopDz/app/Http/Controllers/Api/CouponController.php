<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Validate coupon code
     * @route POST /api/v1/store/{store}/coupons/validate
     */
    public function validateCoupon(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'product_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $coupon = Coupon::where('store_id', $store->id)
            ->where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم غير صالح',
            ], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => $coupon->getInvalidReason(),
            ], 400);
        }

        // Check minimum order
        if ($coupon->min_order_amount && $request->subtotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => "الحد الأدنى للطلب هو {$coupon->min_order_amount} دج",
            ], 400);
        }

        // Check product restrictions
        if ($coupon->product_ids && $request->product_ids) {
            $validProducts = array_intersect($request->product_ids, $coupon->product_ids);
            if (empty($validProducts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كود الخصم لا ينطبق على المنتجات المحددة',
                ], 400);
            }
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'success' => true,
            'message' => 'كود الخصم صالح',
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
                'description' => $coupon->type === 'percentage'
                    ? "خصم {$coupon->value}%"
                    : "خصم {$coupon->value} دج",
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all coupons
     * @route GET /api/v1/dashboard/coupons
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Coupon::where('store_id', $store->id);

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $coupons->items(),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'total' => $coupons->total(),
            ],
        ]);
    }

    /**
     * Create coupon
     * @route POST /api/v1/dashboard/coupons
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'product_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'كود الخصم مطلوب',
            'type.required' => 'نوع الخصم مطلوب',
            'value.required' => 'قيمة الخصم مطلوبة',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check uniqueness within store
        $exists = Coupon::where('store_id', $store->id)
            ->where('code', strtoupper($request->code))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم موجود بالفعل',
            ], 400);
        }

        // Validate percentage
        if ($request->type === 'percentage' && $request->value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'نسبة الخصم لا يمكن أن تتجاوز 100%',
            ], 400);
        }

        $coupon = Coupon::create([
            'store_id' => $store->id,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount,
            'max_discount' => $request->max_discount,
            'max_uses' => $request->max_uses,
            'max_uses_per_customer' => $request->max_uses_per_customer ?? 1,
            'used_count' => 0,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'product_ids' => $request->product_ids,
            'category_ids' => $request->category_ids,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء كود الخصم',
            'data' => $coupon,
        ], 201);
    }

    /**
     * Update coupon
     * @route PUT /api/v1/dashboard/coupons/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $coupon = Coupon::where('store_id', $store->id)->where('id', $id)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'كود الخصم غير موجود'], 404);
        }

        $coupon->update($request->only([
            'type', 'value', 'min_order_amount', 'max_discount',
            'max_uses', 'max_uses_per_customer', 'starts_at',
            'expires_at', 'product_ids', 'category_ids', 'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث كود الخصم',
            'data' => $coupon->fresh(),
        ]);
    }

    /**
     * Delete coupon
     * @route DELETE /api/v1/dashboard/coupons/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $coupon = Coupon::where('store_id', $store->id)->where('id', $id)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'كود الخصم غير موجود'], 404);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف كود الخصم',
        ]);
    }

    /**
     * Toggle coupon status
     * @route PUT /api/v1/dashboard/coupons/{id}/toggle
     */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $coupon = Coupon::where('store_id', $store->id)->where('id', $id)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'كود الخصم غير موجود'], 404);
        }

        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'message' => $coupon->is_active ? 'تم تفعيل كود الخصم' : 'تم إيقاف كود الخصم',
        ]);
    }
}
