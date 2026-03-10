<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Get products for a store (public)
     */
    public function index(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)
            ->orWhere('slug', $storeId)
            ->first();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'المتجر غير موجود',
            ], 404);
        }

        $query = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->with(['category', 'images']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter featured
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        // Filter on sale
        if ($request->has('on_sale')) {
            $query->where('discount_percent', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get featured products
     */
    public function featured(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $products = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('is_featured', true)
            ->with(['category', 'images'])
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get products on sale
     */
    public function onSale(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $products = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('discount_percent', '>', 0)
            ->with(['category', 'images'])
            ->orderBy('discount_percent', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get single product
     */
    public function show(string $storeId, string $id): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $product = Product::where('store_id', $store->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->with(['category', 'images', 'variants'])
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        // Increment views
        $product->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD METHODS (Protected)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get products for dashboard
     */
    public function dashboardIndex(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Product::where('store_id', $store->id)
            ->with(['category', 'images']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Store new product
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check product limit
        if (!$store->canCreateProduct()) {
            return response()->json([
                'success' => false,
                'message' => 'لقد وصلت للحد الأقصى من المنتجات في باقتك الحالية',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'integer|min:0',
            'track_inventory' => 'boolean',
            'status' => 'in:active,draft,archived',
            'is_featured' => 'boolean',
            'discount_percent' => 'integer|min:0|max:100',
            'images' => 'array',
            'images.*' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'cost_price' => $request->cost_price,
            'sku' => $request->sku,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'track_inventory' => $request->track_inventory ?? true,
            'status' => $request->status ?? 'active',
            'is_featured' => $request->is_featured ?? false,
            'discount_percent' => $request->discount_percent ?? 0,
        ]);

        // Add images
        if ($request->has('images')) {
            foreach ($request->images as $index => $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Update store products count
        $store->increment('products_count');

        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المنتج بنجاح',
            'data' => $product,
        ], 201);
    }

    /**
     * Update product
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $product = Product::where('store_id', $store->id)->where('id', $id)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'integer|min:0',
            'track_inventory' => 'boolean',
            'status' => 'in:active,draft,archived',
            'is_featured' => 'boolean',
            'discount_percent' => 'integer|min:0|max:100',
            'images' => 'array',
            'images.*' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update($request->except(['images']));

        // Update images if provided
        if ($request->has('images')) {
            $product->images()->delete();
            foreach ($request->images as $index => $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج بنجاح',
            'data' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $product = Product::where('store_id', $store->id)->where('id', $id)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $product->delete();
        $store->decrement('products_count');

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح',
        ]);
    }

    /**
     * Duplicate product
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $product = Product::where('store_id', $store->id)->where('id', $id)->with('images')->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        // Check limit
        if (!$store->canCreateProduct()) {
            return response()->json([
                'success' => false,
                'message' => 'لقد وصلت للحد الأقصى من المنتجات',
            ], 403);
        }

        // Generate new slug
        $slug = $product->slug . '-copy';
        $counter = 1;
        while (Product::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $product->slug . '-copy-' . $counter++;
        }

        $newProduct = $product->replicate();
        $newProduct->slug = $slug;
        $newProduct->name = $product->name . ' (نسخة)';
        $newProduct->status = 'draft';
        $newProduct->views_count = 0;
        $newProduct->sold_count = 0;
        $newProduct->save();

        // Copy images
        foreach ($product->images as $image) {
            ProductImage::create([
                'product_id' => $newProduct->id,
                'url' => $image->url,
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
            ]);
        }

        $store->increment('products_count');
        $newProduct->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'تم نسخ المنتج بنجاح',
            'data' => $newProduct,
        ], 201);
    }
}
