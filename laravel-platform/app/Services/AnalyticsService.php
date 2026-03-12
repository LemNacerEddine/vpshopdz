<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get store overview stats
     */
    public function getOverview(Store $store, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);
        $previousRange = $this->getPreviousDateRange($period);

        // Current period stats
        $currentOrders = DB::table('orders')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as revenue')
            ->first();

        // Previous period stats (for comparison)
        $previousOrders = DB::table('orders')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $previousRange)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as revenue')
            ->first();

        // Customers
        $currentCustomers = DB::table('customers')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $dateRange)
            ->count();

        $previousCustomers = DB::table('customers')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $previousRange)
            ->count();

        // Visitors
        $currentVisitors = DB::table('browsing_histories')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $dateRange)
            ->distinct('session_id')
            ->count('session_id');

        $previousVisitors = DB::table('browsing_histories')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $previousRange)
            ->distinct('session_id')
            ->count('session_id');

        // Conversion rate
        $conversionRate = $currentVisitors > 0
            ? round(($currentOrders->count / $currentVisitors) * 100, 2)
            : 0;

        return [
            'revenue' => [
                'current' => $currentOrders->revenue,
                'previous' => $previousOrders->revenue,
                'change' => $this->calculateChange($currentOrders->revenue, $previousOrders->revenue),
            ],
            'orders' => [
                'current' => $currentOrders->count,
                'previous' => $previousOrders->count,
                'change' => $this->calculateChange($currentOrders->count, $previousOrders->count),
            ],
            'customers' => [
                'current' => $currentCustomers,
                'previous' => $previousCustomers,
                'change' => $this->calculateChange($currentCustomers, $previousCustomers),
            ],
            'visitors' => [
                'current' => $currentVisitors,
                'previous' => $previousVisitors,
                'change' => $this->calculateChange($currentVisitors, $previousVisitors),
            ],
            'conversion_rate' => $conversionRate,
            'average_order_value' => $currentOrders->count > 0
                ? round($currentOrders->revenue / $currentOrders->count, 2)
                : 0,
        ];
    }

    /**
     * Get sales chart data
     */
    public function getSalesChart(Store $store, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);
        $groupBy = $this->getGroupBy($period);

        $sales = DB::table('orders')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $dateRange)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->selectRaw("{$groupBy} as date_label, COUNT(*) as orders_count, COALESCE(SUM(total), 0) as revenue")
            ->groupByRaw($groupBy)
            ->orderByRaw($groupBy)
            ->get();

        return [
            'labels' => $sales->pluck('date_label')->toArray(),
            'orders' => $sales->pluck('orders_count')->toArray(),
            'revenue' => $sales->pluck('revenue')->toArray(),
        ];
    }

    /**
     * Get top products
     */
    public function getTopProducts(Store $store, int $limit = 10, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $store->id)
            ->whereBetween('orders.created_at', $dateRange)
            ->select(
                'products.id',
                'products.name',
                'products.name_ar',
                'products.image',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.name_ar', 'products.image')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get orders by wilaya
     */
    public function getOrdersByWilaya(Store $store, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('orders')
            ->leftJoin('wilayas', 'orders.wilaya_id', '=', 'wilayas.id')
            ->where('orders.store_id', $store->id)
            ->whereBetween('orders.created_at', $dateRange)
            ->select(
                'wilayas.name_ar as wilaya',
                'wilayas.code',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(orders.total), 0) as revenue')
            )
            ->groupBy('wilayas.id', 'wilayas.name_ar', 'wilayas.code')
            ->orderByDesc('orders_count')
            ->get()
            ->toArray();
    }

    /**
     * Get orders by status
     */
    public function getOrdersByStatus(Store $store, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('orders')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', $dateRange)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function getDateRange(string $period): array
    {
        return match ($period) {
            '7d' => [Carbon::now()->subDays(7), Carbon::now()],
            '30d' => [Carbon::now()->subDays(30), Carbon::now()],
            '90d' => [Carbon::now()->subDays(90), Carbon::now()],
            '1y' => [Carbon::now()->subYear(), Carbon::now()],
            default => [Carbon::now()->subDays(30), Carbon::now()],
        };
    }

    private function getPreviousDateRange(string $period): array
    {
        return match ($period) {
            '7d' => [Carbon::now()->subDays(14), Carbon::now()->subDays(7)],
            '30d' => [Carbon::now()->subDays(60), Carbon::now()->subDays(30)],
            '90d' => [Carbon::now()->subDays(180), Carbon::now()->subDays(90)],
            '1y' => [Carbon::now()->subYears(2), Carbon::now()->subYear()],
            default => [Carbon::now()->subDays(60), Carbon::now()->subDays(30)],
        };
    }

    private function getGroupBy(string $period): string
    {
        return match ($period) {
            '7d' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            '30d' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            '90d' => "DATE_FORMAT(created_at, '%Y-%u')",  // Week
            '1y' => "DATE_FORMAT(created_at, '%Y-%m')",   // Month
            default => "DATE_FORMAT(created_at, '%Y-%m-%d')",
        };
    }

    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
