<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    /**
     * Get store info (public)
     */
    public function show(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)
            ->orWhere('slug', $storeId)
            ->orWhere('subdomain', $storeId)
            ->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'name_ar' => $store->name_ar,
                'slug' => $store->slug,
                'description' => $store->description,
                'logo' => $store->logo,
                'cover_image' => $store->cover_image,
                'currency' => $store->currency,
                'phone' => $store->phone,
                'email' => $store->email,
                'whatsapp' => $store->whatsapp,
                'facebook' => $store->facebook,
                'instagram' => $store->instagram,
                'address' => $store->address,
                'products_count' => $store->products_count,
                'theme_color' => $store->theme_color,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get dashboard overview
     */
    public function dashboard(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Get stats
        $stats = [
            'products_count' => Product::where('store_id', $store->id)->count(),
            'active_products' => Product::where('store_id', $store->id)->where('status', 'active')->count(),
            'customers_count' => Customer::where('store_id', $store->id)->count(),
            'orders_count' => Order::where('store_id', $store->id)->count(),
            'pending_orders' => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'total_revenue' => Order::where('store_id', $store->id)->where('status', 'delivered')->sum('total'),
            'today_orders' => Order::where('store_id', $store->id)->whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('store_id', $store->id)->whereDate('created_at', today())->where('status', 'delivered')->sum('total'),
            'this_month_orders' => Order::where('store_id', $store->id)->whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => Order::where('store_id', $store->id)->whereMonth('created_at', now()->month)->where('status', 'delivered')->sum('total'),
        ];

        // Recent orders
        $recentOrders = Order::where('store_id', $store->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->shipping_name,
                    'total' => $order->total,
                    'status' => $order->status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at,
                ];
            });

        // Top products
        $topProducts = Product::where('store_id', $store->id)
            ->orderBy('sold_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name_ar ?? $product->name,
                    'price' => $product->price,
                    'sold_count' => $product->sold_count,
                    'stock_quantity' => $product->stock_quantity,
                ];
            });

        // Low stock products
        $lowStock = Product::where('store_id', $store->id)
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 5)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        // Subscription info
        $subscription = $store->subscription;

        return response()->json([
            'success' => true,
            'data' => [
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'logo' => $store->logo,
                    'status' => $store->status,
                ],
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'top_products' => $topProducts,
                'low_stock' => $lowStock,
                'subscription' => $subscription ? [
                    'plan' => $subscription->plan->name_ar ?? $subscription->plan->name,
                    'status' => $subscription->status,
                    'ends_at' => $subscription->ends_at,
                    'orders_this_month' => $subscription->orders_this_month,
                    'products_count' => $subscription->products_count,
                ] : null,
            ],
        ]);
    }

    /**
     * Get store settings
     */
    public function settings(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'name_ar' => $store->name_ar,
                'slug' => $store->slug,
                'description' => $store->description,
                'logo' => $store->logo,
                'cover_image' => $store->cover_image,
                'favicon' => $store->favicon,
                'currency' => $store->currency,
                'language' => $store->language,
                'phone' => $store->phone,
                'email' => $store->email,
                'whatsapp' => $store->whatsapp,
                'facebook' => $store->facebook,
                'instagram' => $store->instagram,
                'tiktok' => $store->tiktok,
                'address' => $store->address,
                'theme_color' => $store->theme_color,
                'seo_title' => $store->seo_title,
                'seo_description' => $store->seo_description,
                'facebook_pixel' => $store->facebook_pixel,
                'google_analytics' => $store->google_analytics,
                'custom_domain' => $store->custom_domain,
            ],
        ]);
    }

    /**
     * Update store settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|url',
            'cover_image' => 'nullable|url',
            'favicon' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'whatsapp' => 'nullable|string|max:20',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'address' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:20',
            'seo_title' => 'nullable|string|max:100',
            'seo_description' => 'nullable|string|max:200',
            'facebook_pixel' => 'nullable|string|max:50',
            'google_analytics' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $store->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات المتجر بنجاح',
            'data' => $store->fresh(),
        ]);
    }

    /**
     * Get store analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // Daily orders
        $dailyOrders = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Orders by status
        $ordersByStatus = Order::where('store_id', $store->id)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Top selling products
        $topProducts = Product::where('store_id', $store->id)
            ->orderBy('sold_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'name_ar', 'sold_count', 'price']);

        // Orders by wilaya
        $ordersByWilaya = Order::where('store_id', $store->id)
            ->select('shipping_wilaya', DB::raw('COUNT(*) as count'))
            ->groupBy('shipping_wilaya')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_orders' => $dailyOrders,
                'orders_by_status' => $ordersByStatus,
                'top_products' => $topProducts,
                'orders_by_wilaya' => $ordersByWilaya,
            ],
        ]);
    }

    /**
     * Get customers
     */
    public function customers(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Customer::where('store_id', $store->id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }
}
