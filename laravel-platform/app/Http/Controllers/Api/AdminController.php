<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PLATFORM STATISTICS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get platform overview stats
     * @route GET /api/v1/admin/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'stores' => [
                'total' => Store::count(),
                'active' => Store::where('status', 'active')->count(),
                'suspended' => Store::where('status', 'suspended')->count(),
                'new_today' => Store::whereDate('created_at', today())->count(),
                'new_this_week' => Store::where('created_at', '>=', now()->startOfWeek())->count(),
                'new_this_month' => Store::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'users' => [
                'total' => User::count(),
                'merchants' => User::where('role', 'merchant')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'today' => Order::whereDate('created_at', today())->count(),
                'this_month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
                'total_revenue' => Order::whereIn('status', ['delivered'])->sum('total'),
            ],
            'subscriptions' => [
                'active' => Subscription::where('status', 'active')->count(),
                'total_revenue' => Subscription::where('status', 'active')->sum('price'),
                'by_plan' => SubscriptionPlan::withCount(['subscriptions' => function ($q) {
                    $q->where('status', 'active');
                }])->get(['id', 'name', 'slug']),
            ],
        ];

        // Growth chart data (last 30 days)
        $stats['growth'] = [
            'stores' => Store::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'orders' => Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STORES MANAGEMENT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all stores
     * @route GET /api/v1/admin/stores
     */
    public function stores(Request $request): JsonResponse
    {
        $query = Store::with(['owner', 'plan']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $stores = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $stores->items(),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'total' => $stores->total(),
            ],
        ]);
    }

    /**
     * Get store details
     * @route GET /api/v1/admin/stores/{id}
     */
    public function storeDetails(string $id): JsonResponse
    {
        $store = Store::with(['owner', 'plan', 'products', 'domains'])->find($id);

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $storeData = $store->toArray();
        $storeData['stats'] = [
            'products_count' => $store->products()->count(),
            'orders_count' => Order::where('store_id', $store->id)->count(),
            'total_revenue' => Order::where('store_id', $store->id)->whereIn('status', ['delivered'])->sum('total'),
            'customers_count' => $store->customers()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $storeData,
        ]);
    }

    /**
     * Update store status (suspend/activate/delete)
     * @route PUT /api/v1/admin/stores/{id}/status
     */
    public function updateStoreStatus(Request $request, string $id): JsonResponse
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,suspended,maintenance,closed',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $store->update([
            'status' => $request->status,
            'suspension_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة المتجر',
            'data' => $store->fresh(),
        ]);
    }

    /**
     * Change store plan (admin override)
     * @route PUT /api/v1/admin/stores/{id}/plan
     */
    public function changeStorePlan(Request $request, string $id): JsonResponse
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscription_plans,id',
            'ends_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cancel current subscription
        $current = $store->activeSubscription();
        if ($current) {
            $current->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        // Create new subscription
        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $request->plan_id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 0,
            'currency' => 'DZD',
            'starts_at' => now(),
            'ends_at' => $request->ends_at ?? now()->addYear(),
            'is_trial' => false,
            'auto_renew' => false,
            'notes' => 'تم التعيين بواسطة المدير',
        ]);

        $store->update(['plan_id' => $request->plan_id]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير خطة المتجر',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // USERS MANAGEMENT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all users
     * @route GET /api/v1/admin/users
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::with('store');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Ban/Unban user
     * @route PUT /api/v1/admin/users/{id}/ban
     */
    public function toggleBan(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حظر مدير',
            ], 403);
        }

        $user->update([
            'is_banned' => !$user->is_banned,
            'banned_at' => $user->is_banned ? null : now(),
            'ban_reason' => $request->reason,
        ]);

        // If banned, suspend their store too
        if ($user->is_banned && $user->store) {
            $user->store->update(['status' => 'suspended']);
        }

        return response()->json([
            'success' => true,
            'message' => $user->is_banned ? 'تم حظر المستخدم' : 'تم رفع الحظر عن المستخدم',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // THEMES MANAGEMENT (Admin)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Create theme
     * @route POST /api/v1/admin/themes
     */
    public function createTheme(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:themes',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category' => 'nullable|string|in:general,fashion,electronics,food,services,portfolio',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|string',
            'preview_url' => 'nullable|string',
            'default_colors' => 'nullable|array',
            'default_fonts' => 'nullable|array',
            'default_layout' => 'nullable|array',
            'sections' => 'nullable|array',
            'settings_schema' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $theme = Theme::create(array_merge($request->all(), [
            'is_active' => true,
            'version' => '1.0.0',
            'author' => 'VPShopDZ',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الثيم',
            'data' => $theme,
        ], 201);
    }

    /**
     * Update theme
     * @route PUT /api/v1/admin/themes/{id}
     */
    public function updateTheme(Request $request, string $id): JsonResponse
    {
        $theme = Theme::find($id);

        if (!$theme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير موجود'], 404);
        }

        $theme->update($request->only([
            'name', 'name_ar', 'description', 'description_ar',
            'category', 'is_free', 'price', 'thumbnail', 'preview_url',
            'default_colors', 'default_fonts', 'default_layout',
            'sections', 'settings_schema', 'is_active', 'version',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الثيم',
            'data' => $theme->fresh(),
        ]);
    }

    /**
     * Delete theme
     * @route DELETE /api/v1/admin/themes/{id}
     */
    public function deleteTheme(string $id): JsonResponse
    {
        $theme = Theme::find($id);

        if (!$theme) {
            return response()->json(['success' => false, 'message' => 'الثيم غير موجود'], 404);
        }

        if ($theme->installs_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن حذف الثيم لأنه مثبت في {$theme->installs_count} متجر",
            ], 400);
        }

        $theme->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الثيم',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PLATFORM SETTINGS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get platform settings
     * @route GET /api/v1/admin/settings
     */
    public function settings(): JsonResponse
    {
        $settings = [
            'platform_name' => config('app.name', 'VPShopDZ'),
            'platform_domain' => config('app.domain', 'vpshopdz.com'),
            'default_language' => config('app.locale', 'ar'),
            'supported_languages' => ['ar', 'fr', 'en'],
            'default_currency' => 'DZD',
            'maintenance_mode' => app()->isDownForMaintenance(),
            'registration_enabled' => config('platform.registration_enabled', true),
            'auto_approve_stores' => config('platform.auto_approve_stores', true),
            'default_plan_id' => config('platform.default_plan_id'),
            'trial_days' => config('platform.trial_days', 14),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
