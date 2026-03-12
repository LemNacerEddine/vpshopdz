<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Get categories for a store (public)
     */
    public function index(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)
            ->orWhere('slug', $storeId)
            ->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $categories = Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get category with products
     */
    public function show(string $storeId, string $id): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $category = Category::where('store_id', $store->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->limit(20);
            }])
            ->withCount('products')
            ->first();

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all categories for dashboard
     */
    public function dashboardIndex(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $categories = Category::where('store_id', $store->id)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Create category
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|url',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate slug
        $slug = Str::slug($request->name);
        $counter = 1;
        while (Category::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = Str::slug($request->name) . '-' . $counter++;
        }

        $category = Category::create([
            'store_id' => $store->id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'image' => $request->image,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الفئة بنجاح',
            'data' => $category,
        ], 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $category = Category::where('store_id', $store->id)->where('id', $id)->first();

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|url',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الفئة بنجاح',
            'data' => $category,
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $category = Category::where('store_id', $store->id)->where('id', $id)->first();

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'الفئة غير موجودة'], 404);
        }

        // Check if has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفئة لأنها تحتوي على منتجات',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفئة بنجاح',
        ]);
    }
}
