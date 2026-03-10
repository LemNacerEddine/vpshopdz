<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilaya;
use App\Models\Commune;
use App\Models\ShippingCompany;
use App\Models\ShippingRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Get all wilayas
     * 
     * @route GET /api/v1/store/{storeId}/wilayas
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
     * 
     * @route GET /api/v1/store/{storeId}/communes/{wilayaId}
     */
    public function communes(int $wilayaId): JsonResponse
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
     * Get shipping rates for a store
     * 
     * @route GET /api/v1/store/{storeId}/shipping-rates
     */
    public function rates(Request $request, string $storeId): JsonResponse
    {
        $wilayaId = $request->query('wilaya_id');
        $communeId = $request->query('commune_id');
        $deliveryType = $request->query('delivery_type');

        // Get active shipping companies
        $companies = ShippingCompany::active()
            ->ordered()
            ->get();

        $rates = [];

        foreach ($companies as $company) {
            $query = ShippingRate::where('company_id', $company->id)
                ->active();

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
                $rates[] = [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'name_ar' => $company->name_ar,
                        'code' => $company->code,
                        'logo' => $company->logo,
                    ],
                    'rates' => $companyRates->map(function ($rate) {
                        return [
                            'id' => $rate->id,
                            'delivery_type' => $rate->delivery_type,
                            'price' => $rate->price,
                            'min_days' => $rate->min_days,
                            'max_days' => $rate->max_days,
                            'delivery_days' => $rate->delivery_days,
                        ];
                    }),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * Get all shipping companies (for dashboard)
     * 
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
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $companies,
            'count' => $companies->count(),
        ]);
    }

    /**
     * Update store shipping settings
     * 
     * @route PUT /api/v1/dashboard/shipping/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:shipping_companies,id',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'custom_rates' => 'nullable|array',
        ]);

        // Get current store from auth
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        // Update or create store shipping settings
        $settings = $store->shippingSettings()
            ->updateOrCreate(
                ['company_id' => $validated['company_id']],
                [
                    'api_key' => $validated['api_key'] ?? null,
                    'api_secret' => $validated['api_secret'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                    'custom_rates' => $validated['custom_rates'] ?? null,
                ]
            );

        return response()->json([
            'success' => true,
            'message' => 'Shipping settings updated successfully',
            'data' => $settings,
        ]);
    }
}
