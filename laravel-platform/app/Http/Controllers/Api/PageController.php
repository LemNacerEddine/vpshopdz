<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StorePage;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get published pages for a store
     * @route GET /api/v1/store/{store}/pages
     */
    public function storePages(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $pages = StorePage::where('store_id', $store->id)
            ->where('is_published', true)
            ->select('id', 'title', 'title_ar', 'slug', 'type', 'show_in_header', 'show_in_footer')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Get single page content
     * @route GET /api/v1/store/{store}/pages/{slug}
     */
    public function storePage(string $storeId, string $slug): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $page = StorePage::where('store_id', $store->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'الصفحة غير موجودة'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $page->id,
                'title' => $page->title,
                'title_ar' => $page->title_ar,
                'slug' => $page->slug,
                'content' => $page->content,
                'content_ar' => $page->content_ar,
                'type' => $page->type,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all pages
     * @route GET /api/v1/dashboard/pages
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $pages = StorePage::where('store_id', $store->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Create page
     * @route POST /api/v1/dashboard/pages
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_ar' => 'nullable|string',
            'type' => 'nullable|string|in:custom,about,contact,privacy,terms,faq,shipping,returns',
            'is_published' => 'boolean',
            'show_in_header' => 'boolean',
            'show_in_footer' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        while (StorePage::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $maxOrder = StorePage::where('store_id', $store->id)->max('sort_order') ?? 0;

        $page = StorePage::create([
            'store_id' => $store->id,
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'slug' => $slug,
            'content' => $request->content,
            'content_ar' => $request->content_ar,
            'type' => $request->type ?? 'custom',
            'is_published' => $request->is_published ?? true,
            'show_in_header' => $request->show_in_header ?? false,
            'show_in_footer' => $request->show_in_footer ?? true,
            'sort_order' => $maxOrder + 1,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الصفحة',
            'data' => $page,
        ], 201);
    }

    /**
     * Update page
     * @route PUT /api/v1/dashboard/pages/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $page = StorePage::where('store_id', $store->id)->where('id', $id)->first();

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'الصفحة غير موجودة'], 404);
        }

        $page->update($request->only([
            'title', 'title_ar', 'content', 'content_ar', 'type',
            'is_published', 'show_in_header', 'show_in_footer',
            'sort_order', 'meta_title', 'meta_description',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصفحة',
            'data' => $page->fresh(),
        ]);
    }

    /**
     * Delete page
     * @route DELETE /api/v1/dashboard/pages/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $page = StorePage::where('store_id', $store->id)->where('id', $id)->first();

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'الصفحة غير موجودة'], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصفحة',
        ]);
    }

    /**
     * Reorder pages
     * @route PUT /api/v1/dashboard/pages/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $validator = Validator::make($request->all(), [
            'pages' => 'required|array',
            'pages.*.id' => 'required|string',
            'pages.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->pages as $pageData) {
            StorePage::where('store_id', $store->id)
                ->where('id', $pageData['id'])
                ->update(['sort_order' => $pageData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة ترتيب الصفحات',
        ]);
    }
}
