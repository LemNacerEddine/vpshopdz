<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * Get all subscription plans
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'name_ar' => $plan->name_ar,
                    'description' => $plan->description,
                    'description_ar' => $plan->description_ar,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'features' => [
                        'max_products' => $plan->max_products,
                        'max_orders_per_month' => $plan->max_orders_per_month,
                        'max_staff' => $plan->max_staff,
                        'max_categories' => $plan->max_categories,
                        'max_images_per_product' => $plan->max_images_per_product,
                        'custom_domain' => $plan->custom_domain,
                        'remove_branding' => $plan->remove_branding,
                        'advanced_analytics' => $plan->advanced_analytics,
                        'api_access' => $plan->api_access,
                        'priority_support' => $plan->priority_support,
                        'facebook_pixel' => $plan->facebook_pixel,
                        'whatsapp_integration' => $plan->whatsapp_integration,
                        'abandoned_cart' => $plan->abandoned_cart,
                    ],
                    'trial_days' => $plan->trial_days,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get single plan
     */
    public function show(string $id): JsonResponse
    {
        $plan = SubscriptionPlan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'الباقة غير موجودة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }
}
