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

        // Filter by price - support both min_price/max_price and price_min/price_max
        $minPrice = $request->get('min_price', $request->get('price_min'));
        $maxPrice = $request->get('max_price', $request->get('price_max'));
        if ($minPrice !== null) {
            $query->where('price', '>=', (float)$minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', (float)$maxPrice);
        }

        // Filter featured
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        // Filter on sale / deals
        if ($request->has('on_sale') || $request->has('deals')) {
            $query->where('discount_percent', '>', 0);
        }

        // Sorting - support both 'sort' (storefront) and 'sort_by'/'sort_dir' (dashboard)
        $sortParam = $request->get('sort', $request->get('sort_by', 'newest'));
        switch ($sortParam) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination
        $perPage = min($request->get('limit', $request->get('per_page', 12)), 100);
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


    /**
     * Get new arrivals (latest products)
     */
    public function newArrivals(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $limit = min($request->get('limit', 8), 20);
        $products = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Search products
     */
    public function search(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $q = $request->get('q', $request->get('search', ''));
        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $products = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('name_ar', 'like', "%{$q}%")
                      ->orWhere('name_fr', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
            })
            ->with(['category', 'images'])
            ->limit(20)
            ->get();
        return response()->json(['success' => true, 'data' => $products, 'query' => $q]);
    }

    /**
     * Get related products
     */
    public function related(Request $request, string $storeId, string $id): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $product = Product::where('store_id', $store->id)
            ->where(function ($q) use ($id) { $q->where('id', $id)->orWhere('slug', $id); })
            ->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }
        $related = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                  ->orWhere('is_featured', true);
            })
            ->with(['category', 'images'])
            ->limit(8)
            ->get();
        return response()->json(['success' => true, 'data' => $related]);
    }

    /**
     * Get store categories
     */
    public function categories(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $categories = \App\Models\Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Get single category info
     */
    public function categoryShow(Request $request, string $storeId, string $categoryId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $category = \App\Models\Category::where('store_id', $store->id)
            ->where(function ($q) use ($categoryId) {
                $q->where('id', $categoryId)->orWhere('slug', $categoryId);
            })
            ->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }
        return response()->json(['success' => true, 'data' => $category]);
    }

    /**
     * Get products by category
     */
    public function categoryProducts(Request $request, string $storeId, string $categoryId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        $category = \App\Models\Category::where('store_id', $store->id)
            ->where(function ($q) use ($categoryId) {
                $q->where('id', $categoryId)->orWhere('slug', $categoryId);
            })
            ->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }
        $perPage = min($request->get('per_page', 12), 50);
        $products = Product::where('store_id', $store->id)
            ->where('category_id', $category->id)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'category' => $category,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get single product for dashboard
     */
    public function dashboardShow(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }
        $product = Product::where('store_id', $store->id)
            ->where('id', $id)
            ->with(['category', 'images', 'variants'])
            ->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }
        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Dashboard categories
     */
    public function dashboardCategories(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }
        $categories = \App\Models\Category::where('store_id', $store->id)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Create category
     */
    public function createCategory(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $slug = $request->slug ?: \Illuminate\Support\Str::slug($request->name);
        $counter = 1;
        $baseSlug = $slug;
        while (\App\Models\Category::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $category = \App\Models\Category::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'slug' => $slug,
            'description' => $request->description,
            'image' => $request->image,
            'is_active' => $request->get('is_active', true),
            'sort_order' => \App\Models\Category::where('store_id', $store->id)->count(),
        ]);
        return response()->json(['success' => true, 'data' => $category], 201);
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $category = \App\Models\Category::where('store_id', $store->id)->where('id', $id)->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }
        $category->update($request->only(['name', 'name_ar', 'description', 'image', 'is_active', 'sort_order']));
        return response()->json(['success' => true, 'data' => $category]);
    }

    /**
     * Delete category
     */
    public function deleteCategory(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $category = \App\Models\Category::where('store_id', $store->id)->where('id', $id)->first();
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }
        Product::where('category_id', $category->id)->update(['category_id' => null]);
        $category->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف الفئة']);
    }

    /**
     * Bulk action on products
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $action = $request->get('action');
        $ids = $request->get('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد منتجات'], 422);
        }
        $query = Product::where('store_id', $store->id)->whereIn('id', $ids);
        switch ($action) {
            case 'activate':
                $query->update(['status' => 'active']);
                break;
            case 'deactivate':
                $query->update(['status' => 'draft']);
                break;
            case 'delete':
                $count = $query->count();
                $query->delete();
                $store->decrement('products_count', $count);
                break;
            default:
                return response()->json(['success' => false, 'message' => 'إجراء غير صالح'], 422);
        }
        return response()->json(['success' => true, 'message' => 'تم تنفيذ الإجراء بنجاح']);
    }

    /**
     * Export products
     */
    public function export(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $products = Product::where('store_id', $store->id)
            ->with(['category', 'images'])
            ->get();
        return response()->json(['success' => true, 'data' => $products]);
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
