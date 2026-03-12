<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\DB;

class ShippingService
{
    /**
     * Calculate shipping cost for an order
     */
    public function calculateCost(
        Store $store,
        int $wilayaId,
        ?int $communeId,
        float $orderTotal,
        float $totalWeight,
        string $shippingType = 'home'
    ): array {
        $storeId = $store->id;

        // 1. Check free shipping rules
        $freeShipping = $this->checkFreeShipping($storeId, $wilayaId, $orderTotal, $totalWeight);
        if ($freeShipping) {
            return [
                'cost' => 0,
                'original_cost' => 0,
                'is_free' => true,
                'free_reason' => $freeShipping['reason'],
                'company' => null,
                'estimated_days' => $freeShipping['estimated_days'] ?? '3-5',
            ];
        }

        // 2. Find applicable shipping rates
        $rates = DB::table('shipping_rates')
            ->join('shipping_companies', 'shipping_rates.company_id', '=', 'shipping_companies.id')
            ->where('shipping_rates.store_id', $storeId)
            ->where('shipping_companies.is_active', true)
            ->where('shipping_rates.wilaya_id', $wilayaId)
            ->select(
                'shipping_rates.*',
                'shipping_companies.name as company_name',
                'shipping_companies.name_ar as company_name_ar',
                'shipping_companies.logo as company_logo',
                'shipping_companies.estimated_days'
            )
            ->get();

        if ($rates->isEmpty()) {
            // Fallback: check for default rates (wilaya_id = null means all wilayas)
            $rates = DB::table('shipping_rates')
                ->join('shipping_companies', 'shipping_rates.company_id', '=', 'shipping_companies.id')
                ->where('shipping_rates.store_id', $storeId)
                ->where('shipping_companies.is_active', true)
                ->whereNull('shipping_rates.wilaya_id')
                ->select(
                    'shipping_rates.*',
                    'shipping_companies.name as company_name',
                    'shipping_companies.name_ar as company_name_ar',
                    'shipping_companies.logo as company_logo',
                    'shipping_companies.estimated_days'
                )
                ->get();
        }

        if ($rates->isEmpty()) {
            return [
                'cost' => 0,
                'is_free' => false,
                'error' => 'لا توجد أسعار شحن متاحة لهذه الولاية',
                'available' => false,
            ];
        }

        // 3. Calculate cost for each rate
        $options = [];
        foreach ($rates as $rate) {
            $cost = $shippingType === 'desk' ? $rate->desk_price : $rate->home_price;

            // Apply weight surcharge
            if ($totalWeight > 0 && $rate->extra_weight_price > 0 && $rate->base_weight > 0) {
                $extraWeight = max(0, $totalWeight - $rate->base_weight);
                $cost += ceil($extraWeight) * $rate->extra_weight_price;
            }

            $options[] = [
                'company_id' => $rate->company_id,
                'company_name' => $rate->company_name,
                'company_name_ar' => $rate->company_name_ar,
                'company_logo' => $rate->company_logo,
                'cost' => $cost,
                'shipping_type' => $shippingType,
                'estimated_days' => $rate->estimated_days,
                'is_free' => false,
            ];
        }

        // Sort by cost (cheapest first)
        usort($options, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return [
            'options' => $options,
            'cheapest' => $options[0] ?? null,
            'available' => true,
        ];
    }

    /**
     * Check free shipping rules
     */
    private function checkFreeShipping(string $storeId, int $wilayaId, float $orderTotal, float $totalWeight): ?array
    {
        $rules = DB::table('shipping_rules')
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($rules as $rule) {
            $conditions = json_decode($rule->conditions, true) ?? [];
            $matched = true;

            foreach ($conditions as $condition) {
                switch ($condition['type'] ?? '') {
                    case 'min_order':
                        if ($orderTotal < ($condition['value'] ?? 0)) {
                            $matched = false;
                        }
                        break;

                    case 'max_weight':
                        if ($totalWeight > ($condition['value'] ?? 0)) {
                            $matched = false;
                        }
                        break;

                    case 'wilayas':
                        $allowedWilayas = $condition['value'] ?? [];
                        if (!in_array($wilayaId, $allowedWilayas)) {
                            $matched = false;
                        }
                        break;

                    case 'excluded_wilayas':
                        $excludedWilayas = $condition['value'] ?? [];
                        if (in_array($wilayaId, $excludedWilayas)) {
                            $matched = false;
                        }
                        break;
                }

                if (!$matched) break;
            }

            if ($matched) {
                $action = json_decode($rule->action, true) ?? [];
                if (($action['type'] ?? '') === 'free_shipping') {
                    return [
                        'reason' => $rule->name,
                        'estimated_days' => $action['estimated_days'] ?? '3-5',
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Get all wilayas with shipping availability for a store
     */
    public function getAvailableWilayas(string $storeId): array
    {
        return DB::table('wilayas')
            ->leftJoin('shipping_rates', function ($join) use ($storeId) {
                $join->on('wilayas.id', '=', 'shipping_rates.wilaya_id')
                    ->where('shipping_rates.store_id', '=', $storeId);
            })
            ->select(
                'wilayas.id',
                'wilayas.name',
                'wilayas.name_ar',
                'wilayas.code',
                DB::raw('MIN(shipping_rates.home_price) as min_home_price'),
                DB::raw('MIN(shipping_rates.desk_price) as min_desk_price'),
                DB::raw('COUNT(shipping_rates.id) as rates_count')
            )
            ->groupBy('wilayas.id', 'wilayas.name', 'wilayas.name_ar', 'wilayas.code')
            ->orderBy('wilayas.code')
            ->get()
            ->toArray();
    }
}
