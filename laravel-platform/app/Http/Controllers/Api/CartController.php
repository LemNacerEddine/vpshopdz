<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CartController - Session-based cart for storefront
 * Cart is stored in session (no DB required for guests)
 */
class CartController extends Controller
{
    /**
     * Get cart contents
     * GET /api/v1/store/{store}/cart
     */
    public function show(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $cartKey = "cart_{$store->id}";
        $cart = $request->session()->get($cartKey, []);

        // Enrich cart items with fresh product data
        $enriched = $this->enrichCartItems($cart, $store->id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $enriched['items'],
                'total' => $enriched['total'],
                'count' => $enriched['count'],
            ],
        ]);
    }

    /**
     * Add item to cart
     * POST /api/v1/store/{store}/cart/items
     */
    public function addItem(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));
        $variantId = $request->input('variant_id');

        $product = Product::where('store_id', $store->id)
            ->where(function ($q) use ($productId) {
                $q->where('id', $productId)->orWhere('slug', $productId);
            })
            ->where('status', 'active')
            ->with('images')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود أو غير متاح'], 404);
        }

        // Check stock
        if ($product->track_inventory && $product->stock_quantity < $quantity && !$product->allow_backorder) {
            return response()->json([
                'success' => false,
                'message' => 'الكمية المطلوبة غير متوفرة',
                'available' => $product->stock_quantity,
            ], 422);
        }

        $cartKey = "cart_{$store->id}";
        $cart = $request->session()->get($cartKey, []);

        // Find existing item
        $itemKey = $productId . ($variantId ? "_$variantId" : '');
        $existingIndex = null;
        foreach ($cart as $i => $item) {
            if ($item['product_id'] === $product->id && $item['variant_id'] === $variantId) {
                $existingIndex = $i;
                break;
            }
        }

        $price = $product->final_price ?? $product->price;
        $image = $product->images->where('is_primary', true)->first()?->url
            ?? $product->images->first()?->url;

        if ($existingIndex !== null) {
            $cart[$existingIndex]['quantity'] += $quantity;
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'name' => $product->name,
                'name_ar' => $product->name_ar,
                'price' => $price,
                'original_price' => $product->price,
                'image' => $image,
                'quantity' => $quantity,
                'slug' => $product->slug,
                'stock' => $product->track_inventory ? $product->stock_quantity : 999,
            ];
        }

        $request->session()->put($cartKey, $cart);

        $enriched = $this->enrichCartItems($cart, $store->id);

        return response()->json([
            'success' => true,
            'message' => 'تمت الإضافة إلى السلة',
            'data' => [
                'items' => $enriched['items'],
                'total' => $enriched['total'],
                'count' => $enriched['count'],
            ],
        ]);
    }

    /**
     * Update item quantity
     * PUT /api/v1/store/{store}/cart/items/{itemId}
     */
    public function updateItem(Request $request, string $storeId, string $itemId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $quantity = max(1, (int) $request->input('quantity', 1));
        $cartKey = "cart_{$store->id}";
        $cart = $request->session()->get($cartKey, []);

        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] === $itemId) {
                $item['quantity'] = $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json(['success' => false, 'message' => 'العنصر غير موجود في السلة'], 404);
        }

        $request->session()->put($cartKey, $cart);
        $enriched = $this->enrichCartItems($cart, $store->id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $enriched['items'],
                'total' => $enriched['total'],
                'count' => $enriched['count'],
            ],
        ]);
    }

    /**
     * Remove item from cart
     * DELETE /api/v1/store/{store}/cart/items/{itemId}
     */
    public function removeItem(Request $request, string $storeId, string $itemId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $cartKey = "cart_{$store->id}";
        $cart = $request->session()->get($cartKey, []);
        $cart = array_values(array_filter($cart, fn($item) => $item['product_id'] !== $itemId));
        $request->session()->put($cartKey, $cart);

        $enriched = $this->enrichCartItems($cart, $store->id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $enriched['items'],
                'total' => $enriched['total'],
                'count' => $enriched['count'],
            ],
        ]);
    }

    /**
     * Clear cart
     * DELETE /api/v1/store/{store}/cart
     */
    public function clear(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $cartKey = "cart_{$store->id}";
        $request->session()->forget($cartKey);

        return response()->json([
            'success' => true,
            'message' => 'تم مسح السلة',
            'data' => ['items' => [], 'total' => 0, 'count' => 0],
        ]);
    }

    /**
     * Sync cart from client (merge local storage cart with server)
     * POST /api/v1/store/{store}/cart/sync
     */
    public function sync(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $clientItems = $request->input('items', []);
        $cartKey = "cart_{$store->id}";
        $serverCart = $request->session()->get($cartKey, []);

        // Merge: client items take priority
        $merged = $serverCart;
        foreach ($clientItems as $clientItem) {
            $productId = $clientItem['product_id'] ?? null;
            if (!$productId) continue;

            $found = false;
            foreach ($merged as &$serverItem) {
                if ($serverItem['product_id'] === $productId) {
                    $serverItem['quantity'] = max($serverItem['quantity'], $clientItem['quantity'] ?? 1);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $product = Product::where('store_id', $store->id)
                    ->where('id', $productId)
                    ->where('status', 'active')
                    ->with('images')
                    ->first();

                if ($product) {
                    $price = $product->final_price ?? $product->price;
                    $image = $product->images->where('is_primary', true)->first()?->url
                        ?? $product->images->first()?->url;

                    $merged[] = [
                        'product_id' => $product->id,
                        'variant_id' => $clientItem['variant_id'] ?? null,
                        'name' => $product->name,
                        'name_ar' => $product->name_ar,
                        'price' => $price,
                        'original_price' => $product->price,
                        'image' => $image,
                        'quantity' => max(1, (int)($clientItem['quantity'] ?? 1)),
                        'slug' => $product->slug,
                        'stock' => $product->track_inventory ? $product->stock_quantity : 999,
                    ];
                }
            }
        }

        $request->session()->put($cartKey, $merged);
        $enriched = $this->enrichCartItems($merged, $store->id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $enriched['items'],
                'total' => $enriched['total'],
                'count' => $enriched['count'],
            ],
        ]);
    }

    /**
     * Enrich cart items with fresh product data
     */
    private function enrichCartItems(array $cart, string $storeId): array
    {
        $total = 0;
        $count = 0;
        $items = [];

        foreach ($cart as $item) {
            $product = Product::where('store_id', $storeId)
                ->where('id', $item['product_id'])
                ->with('images')
                ->first();

            if (!$product || $product->status !== 'active') continue;

            $price = $product->final_price ?? $product->price;
            $image = $product->images->where('is_primary', true)->first()?->url
                ?? $product->images->first()?->url;

            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $itemTotal = $price * $quantity;
            $total += $itemTotal;
            $count += $quantity;

            $items[] = [
                'product_id' => $product->id,
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $product->name,
                'name_ar' => $product->name_ar,
                'price' => $price,
                'original_price' => $product->price,
                'discount_percent' => $product->hasActiveDiscount() ? $product->discount_percent : 0,
                'image' => $image,
                'quantity' => $quantity,
                'total' => $itemTotal,
                'slug' => $product->slug,
                'stock' => $product->track_inventory ? $product->stock_quantity : 999,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'name_ar' => $product->name_ar,
                    'price' => $product->price,
                    'final_price' => $price,
                    'discount_percent' => $product->discount_percent,
                    'images' => $product->images->map(fn($img) => ['url' => $img->url, 'is_primary' => $img->is_primary]),
                    'slug' => $product->slug,
                    'stock_quantity' => $product->stock_quantity,
                    'track_inventory' => $product->track_inventory,
                ],
            ];
        }

        return ['items' => $items, 'total' => $total, 'count' => $count];
    }
}
