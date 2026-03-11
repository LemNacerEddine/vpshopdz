<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BrowsingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard overview stats
     * @route GET /api/v1/dashboard/analytics/overview
     */
    public function overview(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays((int)$period);

        // Previous period for comparison
        $prevStartDate = now()->subDays((int)$period * 2);
        $prevEndDate = $startDate;

        // Current period stats
        $currentOrders = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)->count();
        $currentRevenue = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['delivered', 'shipped', 'confirmed'])
            ->sum('total');
        $currentCustomers = Customer::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)->count();

        // Previous period stats
        $prevOrders = Order::where('store_id', $store->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $prevRevenue = Order::where('store_id', $store->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->whereIn('status', ['delivered', 'shipped', 'confirmed'])
            ->sum('total');
        $prevCustomers = Customer::where('store_id', $store->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => [
                    'current' => $currentOrders,
                    'previous' => $prevOrders,
                    'change' => $this->calculateChange($prevOrders, $currentOrders),
                ],
                'revenue' => [
                    'current' => $currentRevenue,
                    'previous' => $prevRevenue,
                    'change' => $this->calculateChange($prevRevenue, $currentRevenue),
                ],
                'customers' => [
                    'current' => $currentCustomers,
                    'previous' => $prevCustomers,
                    'change' => $this->calculateChange($prevCustomers, $currentCustomers),
                ],
                'average_order' => [
                    'current' => $currentOrders > 0 ? round($currentRevenue / $currentOrders, 2) : 0,
                    'previous' => $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0,
                ],
                'total_products' => Product::where('store_id', $store->id)->count(),
                'active_products' => Product::where('store_id', $store->id)->where('is_active', true)->count(),
                'pending_orders' => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * Get sales chart data
     * @route GET /api/v1/dashboard/analytics/sales
     */
    public function sales(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $period = $request->get('period', '30');
        $startDate = now()->subDays((int)$period);
        $groupBy = (int)$period > 60 ? 'month' : 'day';

        $dateFormat = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $sales = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }

    /**
     * Get orders by status
     * @route GET /api/v1/dashboard/analytics/orders-status
     */
    public function ordersByStatus(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $period = $request->get('period', '30');
        $startDate = now()->subDays((int)$period);

        $statuses = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('status')
            ->get();

        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'returned' => 'مرتجع',
            'failed' => 'فشل',
        ];

        $data = $statuses->map(function ($item) use ($statusLabels) {
            return [
                'status' => $item->status,
                'label' => $statusLabels[$item->status] ?? $item->status,
                'count' => $item->count,
                'revenue' => $item->revenue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get top products
     * @route GET /api/v1/dashboard/analytics/top-products
     */
    public function topProducts(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $period = $request->get('period', '30');
        $limit = $request->get('limit', 10);
        $startDate = now()->subDays((int)$period);

        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.created_at', '>=', $startDate)
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->selectRaw('products.id, products.name, products.name_ar, products.price, 
                         SUM(order_items.quantity) as total_sold, 
                         SUM(order_items.total) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.name_ar', 'products.price')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get sales by wilaya (geographic distribution)
     * @route GET /api/v1/dashboard/analytics/by-wilaya
     */
    public function byWilaya(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $period = $request->get('period', '30');
        $startDate = now()->subDays((int)$period);

        $data = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('wilaya_id')
            ->selectRaw('wilaya_id, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('wilaya_id')
            ->orderByDesc('orders')
            ->with('wilaya:id,name_ar,name_fr')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get traffic analytics (visitors, page views, etc.)
     * @route GET /api/v1/dashboard/analytics/traffic
     */
    public function traffic(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store->canUseFeature('advanced_analytics')) {
            return response()->json([
                'success' => false,
                'message' => 'تحليلات الزوار متوفرة في الخطط المتقدمة فقط',
                'upgrade_required' => true,
            ], 403);
        }

        $period = $request->get('period', '30');
        $startDate = now()->subDays((int)$period);

        $visitors = BrowsingHistory::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw("DATE(created_at) as date, 
                         COUNT(*) as page_views, 
                         COUNT(DISTINCT session_id) as unique_visitors")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byDevice = BrowsingHistory::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('device_type, COUNT(DISTINCT session_id) as visitors')
            ->groupBy('device_type')
            ->get();

        $topPages = BrowsingHistory::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('page_type, COUNT(*) as views')
            ->groupBy('page_type')
            ->orderByDesc('views')
            ->get();

        $topReferrers = BrowsingHistory::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('referrer')
            ->selectRaw('referrer, COUNT(*) as visits')
            ->groupBy('referrer')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'daily' => $visitors,
                'by_device' => $byDevice,
                'top_pages' => $topPages,
                'top_referrers' => $topReferrers,
                'totals' => [
                    'page_views' => BrowsingHistory::where('store_id', $store->id)
                        ->where('created_at', '>=', $startDate)->count(),
                    'unique_visitors' => BrowsingHistory::where('store_id', $store->id)
                        ->where('created_at', '>=', $startDate)
                        ->distinct('session_id')->count('session_id'),
                ],
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function calculateChange($previous, $current): array
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
            ];
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        return [
            'value' => abs($change),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
