<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilaya;
use App\Models\Commune;
use App\Models\ShippingCompany;
use App\Models\ShippingRate;
use App\Models\ShippingRule;
use App\Models\Store;
use App\Models\StoreShippingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all wilayas
     * @route GET /api/v1/store/{store}/wilayas
     */
    public function wilayas(): JsonResponse
    {
        $wilayas = Wilaya::orderBy('id')
            ->get()
            ->map(function ($wilaya) {
                return [
                    'id' => $wilaya->id,
                    'name_ar' => $wilaya->name_ar,
                    'name_fr' => $wilaya->name_fr,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $wilayas,
            'count' => $wilayas->count(),
        ]);
    }

    /**
     * Get communes by wilaya
     * @route GET /api/v1/store/{store}/communes/{wilayaId}
     */
    public function communes(string $storeId, int $wilayaId): JsonResponse
    {
        $communes = Commune::where('wilaya_id', $wilayaId)
            ->orderBy('name_ar')
            ->get()
            ->map(function ($commune) {
                return [
                    'id' => $commune->id,
                    'name_ar' => $commune->name_ar,
                    'name_fr' => $commune->name_fr,
                    'postal_code' => $commune->postal_code,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $communes,
            'count' => $communes->count(),
        ]);
    }

    /**
     * Get shipping rates for a store (with advanced calculation)
     * @route GET /api/v1/store/{store}/shipping-rates
     */
    public function rates(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $wilayaId = $request->query('wilaya_id');
        $communeId = $request->query('commune_id');
        $deliveryType = $request->query('delivery_type');
        $totalWeight = (float) $request->query('weight', 0);
        $cartTotal = (float) $request->query('cart_total', 0);
        $productIds = $request->query('product_ids', []);

        // Get store's active shipping companies
        $storeShippingSettings = StoreShippingSetting::where('store_id', $store->id)
            ->where('is_active', true)
            ->with('company')
            ->get();

        // If store has no custom settings, use all active companies
        if ($storeShippingSettings->isEmpty()) {
            $companies = ShippingCompany::active()->ordered()->get();
        } else {
            $companies = $storeShippingSettings->map->company->filter();
        }

        $rates = [];

        foreach ($companies as $company) {
            $query = ShippingRate::where('company_id', $company->id)->active();

            if ($wilayaId) {
                $query->where('wilaya_id', $wilayaId);
            }

            if ($communeId) {
                $query->where(function ($q) use ($communeId) {
                    $q->where('commune_id', $communeId)
                      ->orWhereNull('commune_id');
                });
            }

            if ($deliveryType) {
                $query->where('delivery_type', $deliveryType);
            }

            $companyRates = $query->get();

            if ($companyRates->isNotEmpty()) {
                $processedRates = $companyRates->map(function ($rate) use ($totalWeight, $cartTotal, $store) {
                    $finalPrice = $this->calculateShippingPrice($rate, $totalWeight, $cartTotal, $store);

                    return [
                        'id' => $rate->id,
                        'delivery_type' => $rate->delivery_type,
                        'delivery_type_label' => $this->getDeliveryTypeLabel($rate->delivery_type),
                        'base_price' => $rate->price,
                        'final_price' => $finalPrice,
                        'is_free' => $finalPrice <= 0,
                        'min_days' => $rate->min_days,
                        'max_days' => $rate->max_days,
                        'delivery_days' => $rate->delivery_days,
                    ];
                });

                $rates[] = [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'name_ar' => $company->name_ar,
                        'code' => $company->code,
                        'logo' => $company->logo,
                    ],
                    'rates' => $processedRates,
                ];
            }
        }

        // Check store-level free shipping rules
        $freeShippingInfo = $this->checkFreeShipping($store, $cartTotal, $productIds);

        return response()->json([
            'success' => true,
            'data' => [
                'rates' => $rates,
                'free_shipping' => $freeShippingInfo,
            ],
        ]);
    }

    /**
     * Calculate shipping cost for checkout
     * @route POST /api/v1/store/{store}/shipping/calculate
     */
    public function calculate(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'wilaya_id' => 'required|exists:wilayas,id',
            'commune_id' => 'nullable|exists:communes,id',
            'delivery_type' => 'required|in:home,office,pickup',
            'company_id' => 'nullable|exists:shipping_companies,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Calculate total weight
        $totalWeight = collect($request->items)->sum(function ($item) {
            return ($item['weight'] ?? 0) * $item['quantity'];
        });

        // Calculate cart total for free shipping check
        $cartTotal = collect($request->items)->sum(function ($item) {
            $product = \App\Models\Product::find($item['product_id']);
            return $product ? $product->final_price * $item['quantity'] : 0;
        });

        $productIds = collect($request->items)->pluck('product_id')->toArray();

        // Find the best rate
        $query = ShippingRate::active()
            ->where('wilaya_id', $request->wilaya_id)
            ->where('delivery_type', $request->delivery_type);

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->commune_id) {
            $query->where(function ($q) use ($request) {
                $q->where('commune_id', $request->commune_id)
                  ->orWhereNull('commune_id');
            });
        }

        $rate = $query->orderBy('price')->first();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'لا تتوفر خدمة شحن لهذه المنطقة',
            ], 404);
        }

        $shippingPrice = $this->calculateShippingPrice($rate, $totalWeight, $cartTotal, $store);

        // Check free shipping
        $freeShipping = $this->checkFreeShipping($store, $cartTotal, $productIds);

        if ($freeShipping['eligible']) {
            $shippingPrice = 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'shipping_price' => $shippingPrice,
                'is_free' => $shippingPrice <= 0,
                'free_shipping_reason' => $freeShipping['eligible'] ? $freeShipping['reason'] : null,
                'delivery_type' => $request->delivery_type,
                'estimated_days' => [
                    'min' => $rate->min_days,
                    'max' => $rate->max_days,
                ],
                'company' => [
                    'id' => $rate->company_id,
                    'name' => $rate->company->name ?? '',
                ],
                'weight' => [
                    'total' => $totalWeight,
                    'unit' => 'kg',
                ],
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all shipping companies
     * @route GET /api/v1/dashboard/shipping/companies
     */
    public function companies(): JsonResponse
    {
        $companies = ShippingCompany::ordered()
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'name_ar' => $company->name_ar,
                    'code' => $company->code,
                    'logo' => $company->logo,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'website' => $company->website,
                    'has_api' => !empty($company->api_url),
                    'is_active' => $company->is_active,
                    'tracking_url' => $company->tracking_url,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $companies,
            'count' => $companies->count(),
        ]);
    }

    /**
     * Get store shipping settings
     * @route GET /api/v1/dashboard/shipping/settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $settings = StoreShippingSetting::where('store_id', $store->id)
            ->with('company')
            ->get();

        $shippingRules = ShippingRule::where('store_id', $store->id)
            ->orderBy('priority')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'company_settings' => $settings,
                'shipping_rules' => $shippingRules,
                'global_settings' => [
                    'free_shipping_threshold' => $store->shipping_settings['free_shipping_threshold'] ?? null,
                    'free_shipping_enabled' => $store->shipping_settings['free_shipping_enabled'] ?? false,
                    'weight_threshold' => $store->shipping_settings['weight_threshold'] ?? 5,
                    'extra_weight_price' => $store->shipping_settings['extra_weight_price'] ?? 50,
                    'default_delivery_type' => $store->shipping_settings['default_delivery_type'] ?? 'home',
                ],
            ],
        ]);
    }

    /**
     * Update store shipping settings
     * @route PUT /api/v1/dashboard/shipping/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:shipping_companies,id',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'custom_rates' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = StoreShippingSetting::updateOrCreate(
            [
                'store_id' => $store->id,
                'company_id' => $request->company_id,
            ],
            [
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'is_active' => $request->is_active ?? true,
                'custom_rates' => $request->custom_rates,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات الشحن بنجاح',
            'data' => $settings,
        ]);
    }

    /**
     * Update global shipping settings
     * @route PUT /api/v1/dashboard/shipping/global-settings
     */
    public function updateGlobalSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'free_shipping_enabled' => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'weight_threshold' => 'nullable|numeric|min:0',
            'extra_weight_price' => 'nullable|numeric|min:0',
            'default_delivery_type' => 'nullable|in:home,office,pickup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $currentSettings = $store->shipping_settings ?? [];
        $newSettings = array_merge($currentSettings, $request->only([
            'free_shipping_enabled',
            'free_shipping_threshold',
            'weight_threshold',
            'extra_weight_price',
            'default_delivery_type',
        ]));

        $store->update(['shipping_settings' => $newSettings]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات الشحن العامة',
            'data' => $newSettings,
        ]);
    }

    /**
     * Manage shipping rules (free shipping rules)
     * @route POST /api/v1/dashboard/shipping/rules
     */
    public function storeRule(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:free_shipping,flat_rate,weight_based,quantity_based',
            'conditions' => 'required|array',
            'conditions.min_order_amount' => 'nullable|numeric|min:0',
            'conditions.min_quantity' => 'nullable|integer|min:1',
            'conditions.product_ids' => 'nullable|array',
            'conditions.category_ids' => 'nullable|array',
            'conditions.wilaya_ids' => 'nullable|array',
            'conditions.max_weight' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $rule = ShippingRule::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'type' => $request->type,
            'conditions' => $request->conditions,
            'value' => $request->value ?? 0,
            'is_active' => $request->is_active ?? true,
            'priority' => $request->priority ?? 0,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء قاعدة الشحن بنجاح',
            'data' => $rule,
        ], 201);
    }

    /**
     * Update shipping rule
     * @route PUT /api/v1/dashboard/shipping/rules/{id}
     */
    public function updateRule(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $rule = ShippingRule::where('store_id', $store->id)->where('id', $id)->first();

        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'القاعدة غير موجودة'], 404);
        }

        $rule->update($request->only([
            'name', 'type', 'conditions', 'value', 'is_active', 'priority', 'starts_at', 'ends_at'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث قاعدة الشحن',
            'data' => $rule->fresh(),
        ]);
    }

    /**
     * Delete shipping rule
     * @route DELETE /api/v1/dashboard/shipping/rules/{id}
     */
    public function destroyRule(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $rule = ShippingRule::where('store_id', $store->id)->where('id', $id)->first();

        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'القاعدة غير موجودة'], 404);
        }

        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف قاعدة الشحن',
        ]);
    }

    /**
     * Get shipping rates for a specific wilaya (dashboard)
     * @route GET /api/v1/dashboard/shipping/rates/{wilayaId}
     */
    public function ratesByWilaya(Request $request, int $wilayaId): JsonResponse
    {
        $rates = ShippingRate::where('wilaya_id', $wilayaId)
            ->with('company')
            ->active()
            ->get()
            ->groupBy('company_id')
            ->map(function ($companyRates) {
                $company = $companyRates->first()->company;
                return [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'name_ar' => $company->name_ar,
                    ],
                    'rates' => $companyRates->map(fn($r) => [
                        'id' => $r->id,
                        'delivery_type' => $r->delivery_type,
                        'price' => $r->price,
                        'min_days' => $r->min_days,
                        'max_days' => $r->max_days,
                    ]),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Calculate shipping price with weight-based pricing
     */
    private function calculateShippingPrice(ShippingRate $rate, float $totalWeight, float $cartTotal, Store $store): float
    {
        $basePrice = $rate->price;
        $settings = $store->shipping_settings ?? [];

        // Weight-based pricing
        if ($totalWeight > 0) {
            $weightThreshold = $settings['weight_threshold'] ?? 5; // kg
            $extraWeightPrice = $settings['extra_weight_price'] ?? 50; // DZD per kg

            if ($totalWeight > $weightThreshold) {
                $extraWeight = $totalWeight - $weightThreshold;
                $basePrice += ceil($extraWeight) * $extraWeightPrice;
            }
        }

        return max(0, $basePrice);
    }

    /**
     * Check if free shipping applies
     */
    private function checkFreeShipping(Store $store, float $cartTotal, array $productIds = []): array
    {
        $result = [
            'eligible' => false,
            'reason' => null,
            'threshold' => null,
            'remaining' => null,
        ];

        $settings = $store->shipping_settings ?? [];

        // Check global free shipping threshold
        if (!empty($settings['free_shipping_enabled']) && !empty($settings['free_shipping_threshold'])) {
            $threshold = (float) $settings['free_shipping_threshold'];
            $result['threshold'] = $threshold;

            if ($cartTotal >= $threshold) {
                $result['eligible'] = true;
                $result['reason'] = 'تجاوز الحد الأدنى للشحن المجاني';
                return $result;
            } else {
                $result['remaining'] = $threshold - $cartTotal;
            }
        }

        // Check shipping rules
        $rules = ShippingRule::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('type', 'free_shipping')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {
            $conditions = $rule->conditions ?? [];

            // Check min order amount
            if (!empty($conditions['min_order_amount']) && $cartTotal < $conditions['min_order_amount']) {
                continue;
            }

            // Check product-specific free shipping
            if (!empty($conditions['product_ids'])) {
                $matchingProducts = array_intersect($productIds, $conditions['product_ids']);
                if (empty($matchingProducts)) {
                    continue;
                }
            }

            $result['eligible'] = true;
            $result['reason'] = $rule->name;
            return $result;
        }

        return $result;
    }

    /**
     * Get delivery type label
     */
    private function getDeliveryTypeLabel(string $type): string
    {
        return match($type) {
            'home' => 'توصيل للمنزل',
            'office' => 'توصيل للمكتب',
            'pickup' => 'استلام من نقطة التسليم',
            default => $type,
        };
    }

    /**
     * Get public shipping companies for storefront
     */
    public function publicCompanies(string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }
        // Get store-specific shipping settings
        $storeSettings = StoreShippingSetting::where('store_id', $store->id)
            ->where('is_active', true)
            ->with('company')
            ->get();
        if ($storeSettings->isNotEmpty()) {
            $companies = $storeSettings->map(function ($setting) {
                return [
                    'id' => $setting->company->id,
                    'name' => $setting->company->name,
                    'logo' => $setting->company->logo ?? null,
                    'delivery_types' => $setting->company->delivery_types ?? ['home', 'office'],
                ];
            })->filter()->values();
        } else {
            $companies = ShippingCompany::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'logo' => $c->logo ?? null,
                        'delivery_types' => $c->delivery_types ?? ['home', 'office'],
                    ];
                });
        }
        return response()->json(['success' => true, 'data' => $companies]);
    }

    /**
     * Get shipping settings for dashboard
     */
    public function dashboardGetSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }
        $settings = StoreShippingSetting::where('store_id', $store->id)
            ->with('company')
            ->get();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    /**
     * Update shipping settings for dashboard
     */
    public function dashboardUpdateSettings(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }
        $companies = $request->get('companies', []);
        // Delete existing settings
        StoreShippingSetting::where('store_id', $store->id)->delete();
        // Create new settings
        foreach ($companies as $companyData) {
            StoreShippingSetting::create([
                'store_id' => $store->id,
                'company_id' => $companyData['company_id'],
                'is_active' => $companyData['is_active'] ?? true,
            ]);
        }
        return response()->json(['success' => true, 'message' => 'تم تحديث إعدادات الشحن']);
    }

    /**
     * Toggle shipping company for store
     */
    public function toggleCompany(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $setting = StoreShippingSetting::where('store_id', $store->id)->where('company_id', $id)->first();
        if (!$setting) {
            $setting = StoreShippingSetting::create([
                'store_id' => $store->id,
                'company_id' => $id,
                'is_active' => true,
            ]);
        } else {
            $setting->update(['is_active' => $setting->is_active]);
        }
        return response()->json(['success' => true, 'data' => $setting]);
    }

    /**
     * Get shipping rates for dashboard
     */
    public function dashboardRates(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $rates = ShippingRate::where(function ($q) use ($store) {
                // Get rates for companies active in this store
                $companyIds = StoreShippingSetting::where('store_id', $store->id)
                    ->where('is_active', true)
                    ->pluck('company_id');
                if ($companyIds->isNotEmpty()) {
                    $q->whereIn('company_id', $companyIds);
                }
            })
            ->with(['wilaya', 'commune', 'company'])
            ->orderBy('wilaya_id')
            ->paginate(50);
        return response()->json(['success' => true, 'data' => $rates]);
    }

    /**
     * Create shipping rate
     */
    public function createRate(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'company_id' => 'required|exists:shipping_companies,id',
            'wilaya_id' => 'required|exists:wilayas,id',
            'delivery_type' => 'required|in:home,office,pickup',
            'price' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $rate = ShippingRate::create($request->all());
        return response()->json(['success' => true, 'data' => $rate], 201);
    }

    /**
     * Update shipping rate
     */
    public function updateRate(Request $request, string $id): JsonResponse
    {
        $rate = ShippingRate::find($id);
        if (!$rate) {
            return response()->json(['success' => false, 'message' => 'السعر غير موجود'], 404);
        }
        $rate->update($request->only(['price', 'min_days', 'max_days', 'is_active']));
        return response()->json(['success' => true, 'data' => $rate]);
    }

    /**
     * Delete shipping rate
     */
    public function deleteRate(Request $request, string $id): JsonResponse
    {
        $rate = ShippingRate::find($id);
        if (!$rate) {
            return response()->json(['success' => false, 'message' => 'السعر غير موجود'], 404);
        }
        $rate->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف السعر']);
    }

    /**
     * Bulk create rates
     */
    public function bulkCreateRates(Request $request): JsonResponse
    {
        $rates = $request->get('rates', []);
        $created = 0;
        foreach ($rates as $rateData) {
            ShippingRate::updateOrCreate(
                [
                    'company_id' => $rateData['company_id'],
                    'wilaya_id' => $rateData['wilaya_id'],
                    'delivery_type' => $rateData['delivery_type'],
                ],
                $rateData
            );
            $created++;
        }
        return response()->json(['success' => true, 'message' => "تم إنشاء {$created} سعر"]);
    }

    /**
     * Import rates
     */
    public function importRates(Request $request): JsonResponse
    {
        return $this->bulkCreateRates($request);
    }

    /**
     * Get shipping rules
     */
    public function rules(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $rules = \App\Models\ShippingRule::where('store_id', $store->id)->get();
        return response()->json(['success' => true, 'data' => $rules]);
    }

    /**
     * Create shipping rule
     */
    public function createRule(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $rule = \App\Models\ShippingRule::create(array_merge($request->all(), ['store_id' => $store->id]));
        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    /**
     * Update shipping rule
     */
    public function dashboardUpdateRule(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $rule = \App\Models\ShippingRule::where('store_id', $store->id)->where('id', $id)->first();
        if (!$rule) return response()->json(['success' => false, 'message' => 'القاعدة غير موجودة'], 404);
        $rule->update($request->all());
        return response()->json(['success' => true, 'data' => $rule]);
    }

    /**
     * Delete shipping rule
     */
    public function deleteRule(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $rule = \App\Models\ShippingRule::where('store_id', $store->id)->where('id', $id)->first();
        if (!$rule) return response()->json(['success' => false, 'message' => 'القاعدة غير موجودة'], 404);
        $rule->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف']);
    }

    /**
     * Create shipping company
     */
    public function createCompany(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $company = ShippingCompany::create($request->all());
        return response()->json(['success' => true, 'data' => $company], 201);
    }

    /**
     * Update shipping company
     */
    public function updateCompany(Request $request, string $id): JsonResponse
    {
        $company = ShippingCompany::find($id);
        if (!$company) return response()->json(['success' => false, 'message' => 'شركة الشحن غير موجودة'], 404);
        $company->update($request->all());
        return response()->json(['success' => true, 'data' => $company]);
    }

    /**
     * Delete shipping company
     */
    public function deleteCompany(Request $request, string $id): JsonResponse
    {
        $company = ShippingCompany::find($id);
        if (!$company) return response()->json(['success' => false, 'message' => 'شركة الشحن غير موجودة'], 404);
        $company->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف']);
    }

}
