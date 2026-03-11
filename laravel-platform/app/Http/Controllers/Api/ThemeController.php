<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\StoreTheme;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront) - Get theme config for rendering
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get active theme configuration for a store
     * @route GET /api/v1/store/{store}/theme
     */
    public function storeTheme(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $themeConfig = $store->getThemeConfig();

        return response()->json([
            'success' => true,
            'data' => $themeConfig,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get available themes catalog
     * @route GET /api/v1/dashboard/themes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Theme::active()->ordered();

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by price
        if ($request->has('free_only') && $request->free_only) {
            $query->free();
        }

        $themes = $query->get()->map(function ($theme) use ($request) {
            $store = $request->user()->store ?? null;
            $isInstalled = false;

            if ($store) {
                $isInstalled = StoreTheme::where('store_id', $store->id)
                    ->where('theme_id', $theme->id)
                    ->exists();
            }

            return [
                'id' => $theme->id,
                'name' => $theme->name,
                'name_ar' => $theme->name_ar,
                'slug' => $theme->slug,
                'description' => $theme->description,
                'description_ar' => $theme->description_ar,
                'thumbnail' => $theme->thumbnail,
                'preview_url' => $theme->preview_url,
                'category' => $theme->category,
                'is_free' => $theme->is_free,
                'price' => $theme->price,
                'is_default' => $theme->is_default,
                'installs_count' => $theme->installs_count,
                'version' => $theme->version,
                'author' => $theme->author,
                'is_installed' => $isInstalled,
                'settings_schema' => $theme->settings_schema,
            ];
        });

        // Get categories for filter
        $categories = Theme::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter();

        return response()->json([
            'success' => true,
            'data' => $themes,
            'categories' => $categories,
        ]);
    }

    /**
     * Get single theme details
     * @route GET /api/v1/dashboard/themes/{id}
     */
    public function show(string $id): JsonResponse
    {
        $theme = Theme::where('id', $id)->orWhere('slug', $id)->first();

        if (!$theme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير موجود'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $theme,
        ]);
    }

    /**
     * Install theme to store
     * @route POST /api/v1/dashboard/themes/{id}/install
     */
    public function install(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $theme = Theme::where('id', $id)->orWhere('slug', $id)->first();

        if (!$theme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير موجود'], 404);
        }

        // Check if already installed
        $existing = StoreTheme::where('store_id', $store->id)
            ->where('theme_id', $theme->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'الثيم مثبت بالفعل',
            ], 400);
        }

        // Check if theme is paid and store has access
        if (!$theme->is_free) {
            if (!$store->canUseFeature('premium_themes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'الثيمات المدفوعة غير متوفرة في خطتك الحالية',
                    'upgrade_required' => true,
                ], 403);
            }
        }

        // Deactivate current theme
        StoreTheme::where('store_id', $store->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Install and activate new theme
        $storeTheme = StoreTheme::create([
            'store_id' => $store->id,
            'theme_id' => $theme->id,
            'is_active' => true,
            'custom_colors' => $theme->default_colors,
            'custom_fonts' => $theme->default_fonts,
            'custom_layout' => $theme->default_layout,
            'custom_sections' => $theme->sections,
            'header_settings' => [],
            'footer_settings' => [],
            'homepage_settings' => [],
        ]);

        // Increment install count
        $theme->increment('installs_count');

        return response()->json([
            'success' => true,
            'message' => 'تم تثبيت وتفعيل الثيم بنجاح',
            'data' => $storeTheme,
        ]);
    }

    /**
     * Activate an installed theme
     * @route PUT /api/v1/dashboard/themes/{id}/activate
     */
    public function activate(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $storeTheme = StoreTheme::where('store_id', $store->id)
            ->where('id', $id)
            ->first();

        if (!$storeTheme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير مثبت'], 404);
        }

        // Deactivate all
        StoreTheme::where('store_id', $store->id)->update(['is_active' => false]);

        // Activate this one
        $storeTheme->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل الثيم',
        ]);
    }

    /**
     * Get store's installed themes
     * @route GET /api/v1/dashboard/themes/installed
     */
    public function installed(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $themes = StoreTheme::where('store_id', $store->id)
            ->with('theme')
            ->get()
            ->map(function ($storeTheme) {
                return [
                    'id' => $storeTheme->id,
                    'theme_id' => $storeTheme->theme_id,
                    'theme_name' => $storeTheme->theme->name ?? '',
                    'theme_name_ar' => $storeTheme->theme->name_ar ?? '',
                    'thumbnail' => $storeTheme->theme->thumbnail ?? '',
                    'is_active' => $storeTheme->is_active,
                    'custom_colors' => $storeTheme->custom_colors,
                    'custom_fonts' => $storeTheme->custom_fonts,
                    'installed_at' => $storeTheme->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $themes,
        ]);
    }

    /**
     * Customize active theme
     * @route PUT /api/v1/dashboard/themes/customize
     */
    public function customize(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $storeTheme = StoreTheme::where('store_id', $store->id)
            ->where('is_active', true)
            ->first();

        if (!$storeTheme) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد ثيم مفعل. يرجى تثبيت ثيم أولاً.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'colors' => 'nullable|array',
            'colors.primary' => 'nullable|string',
            'colors.secondary' => 'nullable|string',
            'colors.accent' => 'nullable|string',
            'colors.background' => 'nullable|string',
            'colors.text' => 'nullable|string',
            'colors.header_bg' => 'nullable|string',
            'colors.footer_bg' => 'nullable|string',
            'fonts' => 'nullable|array',
            'fonts.heading' => 'nullable|string',
            'fonts.body' => 'nullable|string',
            'layout' => 'nullable|array',
            'layout.style' => 'nullable|string|in:modern,classic,minimal,bold',
            'layout.product_grid' => 'nullable|string|in:2,3,4',
            'layout.show_sidebar' => 'nullable|boolean',
            'layout.rtl' => 'nullable|boolean',
            'sections' => 'nullable|array',
            'header_settings' => 'nullable|array',
            'header_settings.show_search' => 'nullable|boolean',
            'header_settings.show_cart' => 'nullable|boolean',
            'header_settings.show_language' => 'nullable|boolean',
            'header_settings.announcement_text' => 'nullable|string',
            'header_settings.announcement_bg' => 'nullable|string',
            'footer_settings' => 'nullable|array',
            'footer_settings.show_social' => 'nullable|boolean',
            'footer_settings.show_newsletter' => 'nullable|boolean',
            'footer_settings.copyright_text' => 'nullable|string',
            'homepage_settings' => 'nullable|array',
            'homepage_settings.show_hero' => 'nullable|boolean',
            'homepage_settings.hero_title' => 'nullable|string',
            'homepage_settings.hero_subtitle' => 'nullable|string',
            'homepage_settings.hero_image' => 'nullable|string',
            'homepage_settings.show_featured' => 'nullable|boolean',
            'homepage_settings.show_categories' => 'nullable|boolean',
            'homepage_settings.show_testimonials' => 'nullable|boolean',
            'custom_css' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = [];

        if ($request->has('colors')) {
            $updateData['custom_colors'] = array_merge(
                $storeTheme->custom_colors ?? [],
                $request->colors
            );
        }

        if ($request->has('fonts')) {
            $updateData['custom_fonts'] = array_merge(
                $storeTheme->custom_fonts ?? [],
                $request->fonts
            );
        }

        if ($request->has('layout')) {
            $updateData['custom_layout'] = array_merge(
                $storeTheme->custom_layout ?? [],
                $request->layout
            );
        }

        if ($request->has('sections')) {
            $updateData['custom_sections'] = $request->sections;
        }

        if ($request->has('header_settings')) {
            $updateData['header_settings'] = array_merge(
                $storeTheme->header_settings ?? [],
                $request->header_settings
            );
        }

        if ($request->has('footer_settings')) {
            $updateData['footer_settings'] = array_merge(
                $storeTheme->footer_settings ?? [],
                $request->footer_settings
            );
        }

        if ($request->has('homepage_settings')) {
            $updateData['homepage_settings'] = array_merge(
                $storeTheme->homepage_settings ?? [],
                $request->homepage_settings
            );
        }

        if ($request->has('custom_css')) {
            $updateData['custom_css'] = $request->custom_css;
        }

        $storeTheme->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التخصيصات بنجاح',
            'data' => $storeTheme->fresh(),
        ]);
    }

    /**
     * Reset theme customizations to defaults
     * @route PUT /api/v1/dashboard/themes/reset
     */
    public function reset(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $storeTheme = StoreTheme::where('store_id', $store->id)
            ->where('is_active', true)
            ->with('theme')
            ->first();

        if (!$storeTheme) {
            return response()->json(['success' => false, 'message' => 'لا يوجد ثيم مفعل'], 404);
        }

        $theme = $storeTheme->theme;

        $storeTheme->update([
            'custom_colors' => $theme->default_colors,
            'custom_fonts' => $theme->default_fonts,
            'custom_layout' => $theme->default_layout,
            'custom_sections' => $theme->sections,
            'custom_css' => null,
            'header_settings' => [],
            'footer_settings' => [],
            'homepage_settings' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين الثيم للإعدادات الافتراضية',
        ]);
    }

    /**
     * Uninstall theme
     * @route DELETE /api/v1/dashboard/themes/{id}/uninstall
     */
    public function uninstall(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $storeTheme = StoreTheme::where('store_id', $store->id)
            ->where('id', $id)
            ->first();

        if (!$storeTheme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير مثبت'], 404);
        }

        if ($storeTheme->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الثيم المفعل. قم بتفعيل ثيم آخر أولاً.',
            ], 400);
        }

        $storeTheme->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تثبيت الثيم',
        ]);
    }
}
