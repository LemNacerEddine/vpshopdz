<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * قائمة المنتجات
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId') ?? $request->user()?->store_id;

        if (!$storeId) {
            return response()->json(['success' => false, 'message' => 'المتجر غير محدد'], 400);
        }

        $query = Product::with(['category', 'images'])
            ->forStore($storeId)
            ->active();

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('on_sale')) {
            $query->onSale();
        }

        if ($request->has('in_stock')) {
            $query->inStock();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'price', 'name', 'sold_count', 'rating'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * عرض منتج
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $storeId = $request->route('storeId') ?? $request->user()?->store_id;

        $product = Product::with(['category', 'images', 'variants', 'reviews' => function ($q) {
            $q->where('is_approved', true)->latest()->take(10);
        }])
        ->forStore($storeId)
        ->findOrFail($id);

        // Increment views
        $product->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * إنشاء منتج جديد
     */
    public function store(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,draft',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Product::where('store_id', $storeId)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $product = Product::create([
            'store_id' => $storeId,
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'category_id' => $request->category_id,
            'sku' => $request->sku,
            'stock_quantity' => $request->stock_quantity,
            'discount_percent' => $request->discount_percent ?? 0,
            'is_featured' => $request->is_featured ?? false,
            'status' => $request->status ?? 'active',
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
        $product->store->increment('products_count');

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المنتج بنجاح',
            'data' => $product->load('images', 'category')
        ], 201);
    }

    /**
     * تحديث منتج
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $product = Product::forStore($storeId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->only([
            'name', 'name_ar', 'name_fr', 'description', 'description_ar',
            'price', 'compare_at_price', 'category_id', 'sku',
            'stock_quantity', 'track_inventory', 'discount_percent',
            'discount_starts_at', 'discount_ends_at',
            'is_featured', 'status', 'meta_title', 'meta_description'
        ]));

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

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج',
            'data' => $product->fresh(['images', 'category'])
        ]);
    }

    /**
     * حذف منتج
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $product = Product::forStore($storeId)->findOrFail($id);
        
        $product->delete();

        // Update store products count
        $product->store->decrement('products_count');

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج'
        ]);
    }

    /**
     * المنتجات المميزة
     */
    public function featured(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId');

        $products = Product::with(['images'])
            ->forStore($storeId)
            ->active()
            ->featured()
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * المنتجات ذات التخفيضات
     */
    public function onSale(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId');

        $products = Product::with(['images'])
            ->forStore($storeId)
            ->active()
            ->onSale()
            ->orderByDesc('discount_percent')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'total' => $products->total(),
            ]
        ]);
    }
}
