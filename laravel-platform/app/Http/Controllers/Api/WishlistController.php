<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WishlistController - Session-based wishlist for storefront
 */
class WishlistController extends Controller
{
    /**
     * Get wishlist
     * GET /api/v1/store/{store}/wishlist
     */
    public function show(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $wishlistKey = "wishlist_{$store->id}";
        $productIds = $request->session()->get($wishlistKey, []);

        $products = Product::where('store_id', $store->id)
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Add item to wishlist
     * POST /api/v1/store/{store}/wishlist/items
     */
    public function addItem(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $productId = $request->input('product_id');
        $product = Product::where('store_id', $store->id)
            ->where(function ($q) use ($productId) {
                $q->where('id', $productId)->orWhere('slug', $productId);
            })
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $wishlistKey = "wishlist_{$store->id}";
        $wishlist = $request->session()->get($wishlistKey, []);

        if (!in_array($product->id, $wishlist)) {
            $wishlist[] = $product->id;
            $request->session()->put($wishlistKey, $wishlist);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت الإضافة إلى قائمة الأمنيات',
            'count' => count($wishlist),
        ]);
    }

    /**
     * Remove item from wishlist
     * DELETE /api/v1/store/{store}/wishlist/items/{productId}
     */
    public function removeItem(Request $request, string $storeId, string $productId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $wishlistKey = "wishlist_{$store->id}";
        $wishlist = $request->session()->get($wishlistKey, []);
        $wishlist = array_values(array_filter($wishlist, fn($id) => $id !== $productId));
        $request->session()->put($wishlistKey, $wishlist);

        return response()->json([
            'success' => true,
            'message' => 'تم الحذف من قائمة الأمنيات',
            'count' => count($wishlist),
        ]);
    }

    /**
     * Toggle item in wishlist
     * POST /api/v1/store/{store}/wishlist/toggle
     */
    public function toggle(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $productId = $request->input('product_id');
        $product = Product::where('store_id', $store->id)
            ->where(function ($q) use ($productId) {
                $q->where('id', $productId)->orWhere('slug', $productId);
            })
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $wishlistKey = "wishlist_{$store->id}";
        $wishlist = $request->session()->get($wishlistKey, []);

        $added = false;
        if (in_array($product->id, $wishlist)) {
            $wishlist = array_values(array_filter($wishlist, fn($id) => $id !== $product->id));
            $message = 'تم الحذف من قائمة الأمنيات';
        } else {
            $wishlist[] = $product->id;
            $added = true;
            $message = 'تمت الإضافة إلى قائمة الأمنيات';
        }

        $request->session()->put($wishlistKey, $wishlist);

        return response()->json([
            'success' => true,
            'added' => $added,
            'message' => $message,
            'count' => count($wishlist),
        ]);
    }
}
