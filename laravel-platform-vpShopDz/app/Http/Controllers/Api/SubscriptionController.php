<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES - View plans
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all available subscription plans
     * @route GET /api/v1/plans
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'currency' => $plan->currency ?? 'DZD',
                    'is_free' => $plan->is_free,
                    'is_popular' => $plan->is_popular,
                    'features' => $plan->features,
                    'limits' => [
                        'products_limit' => $plan->products_limit,
                        'orders_limit' => $plan->orders_limit,
                        'storage_limit_mb' => $plan->storage_limit_mb,
                        'staff_limit' => $plan->staff_limit,
                        'domains_limit' => $plan->domains_limit,
                    ],
                    'feature_flags' => [
                        'custom_domain' => $plan->custom_domain,
                        'remove_branding' => $plan->remove_branding,
                        'premium_themes' => $plan->premium_themes,
                        'custom_pixel' => $plan->custom_pixel,
                        'facebook_ads' => $plan->facebook_ads,
                        'abandoned_cart' => $plan->abandoned_cart,
                        'advanced_analytics' => $plan->advanced_analytics,
                        'api_access' => $plan->api_access,
                        'priority_support' => $plan->priority_support,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get current subscription
     * @route GET /api/v1/dashboard/subscription
     */
    public function current(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $subscription = $store->activeSubscription();
        $plan = $subscription ? $subscription->plan : null;

        // Usage stats
        $usage = [
            'products_used' => $store->products()->count(),
            'products_limit' => $plan->products_limit ?? 10,
            'products_percentage' => 0,
            'orders_used' => $store->ordersThisMonth(),
            'orders_limit' => $plan->orders_limit ?? 50,
            'orders_percentage' => 0,
            'storage_used_mb' => $store->storageUsedMb(),
            'storage_limit_mb' => $plan->storage_limit_mb ?? 100,
            'storage_percentage' => 0,
            'staff_used' => $store->staff()->count(),
            'staff_limit' => $plan->staff_limit ?? 1,
        ];

        if ($usage['products_limit'] > 0) {
            $usage['products_percentage'] = round(($usage['products_used'] / $usage['products_limit']) * 100, 1);
        }
        if ($usage['orders_limit'] > 0) {
            $usage['orders_percentage'] = round(($usage['orders_used'] / $usage['orders_limit']) * 100, 1);
        }
        if ($usage['storage_limit_mb'] > 0) {
            $usage['storage_percentage'] = round(($usage['storage_used_mb'] / $usage['storage_limit_mb']) * 100, 1);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'plan_name' => $plan->name ?? 'مجاني',
                    'plan_slug' => $plan->slug ?? 'free',
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'starts_at' => $subscription->starts_at?->format('Y-m-d'),
                    'ends_at' => $subscription->ends_at?->format('Y-m-d'),
                    'is_trial' => $subscription->is_trial,
                    'trial_ends_at' => $subscription->trial_ends_at?->format('Y-m-d'),
                    'auto_renew' => $subscription->auto_renew,
                    'days_remaining' => $subscription->ends_at ? now()->diffInDays($subscription->ends_at, false) : null,
                ] : null,
                'plan' => $plan,
                'usage' => $usage,
            ],
        ]);
    }

    /**
     * Subscribe to a plan (or upgrade/downgrade)
     * @route POST /api/v1/dashboard/subscription/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'nullable|string|in:cib,edahabia,cash,transfer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Check if upgrading or downgrading
        $currentSubscription = $store->activeSubscription();

        if ($currentSubscription && $currentSubscription->plan_id === $plan->id) {
            return response()->json([
                'success' => false,
                'message' => 'أنت مشترك بالفعل في هذه الخطة',
            ], 400);
        }

        // Cancel current subscription
        if ($currentSubscription) {
            $currentSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        // Calculate price
        $price = $request->billing_cycle === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        // Create new subscription
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => $plan->is_free ? 'active' : 'pending',
            'billing_cycle' => $request->billing_cycle,
            'price' => $price,
            'currency' => 'DZD',
            'starts_at' => now(),
            'ends_at' => $request->billing_cycle === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
            'is_trial' => false,
            'auto_renew' => true,
            'payment_method' => $request->payment_method,
        ]);

        // Update store plan
        $store->update(['plan_id' => $plan->id]);

        // If free plan, activate immediately
        if ($plan->is_free) {
            return response()->json([
                'success' => true,
                'message' => 'تم تفعيل الخطة المجانية بنجاح',
                'data' => $subscription,
            ]);
        }

        // For paid plans, return payment instructions
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الاشتراك. يرجى إتمام الدفع.',
            'data' => [
                'subscription' => $subscription,
                'payment' => [
                    'amount' => $price,
                    'currency' => 'DZD',
                    'method' => $request->payment_method,
                    'instructions' => $this->getPaymentInstructions($request->payment_method, $price),
                ],
            ],
        ]);
    }

    /**
     * Cancel subscription
     * @route PUT /api/v1/dashboard/subscription/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $subscription = $store->activeSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد اشتراك نشط',
            ], 404);
        }

        $subscription->update([
            'auto_renew' => false,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التجديد التلقائي. سيظل اشتراكك نشطاً حتى ' . $subscription->ends_at->format('Y-m-d'),
            'data' => [
                'ends_at' => $subscription->ends_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get subscription history
     * @route GET /api/v1/dashboard/subscription/history
     */
    public function history(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $subscriptions = Subscription::where('store_id', $store->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'plan_name' => $sub->plan->name ?? 'غير معروف',
                    'status' => $sub->status,
                    'billing_cycle' => $sub->billing_cycle,
                    'price' => $sub->price,
                    'starts_at' => $sub->starts_at?->format('Y-m-d'),
                    'ends_at' => $sub->ends_at?->format('Y-m-d'),
                    'cancelled_at' => $sub->cancelled_at?->format('Y-m-d'),
                    'created_at' => $sub->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMIN ROUTES (Super Admin)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get all subscriptions (admin)
     * @route GET /api/v1/admin/subscriptions
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Subscription::with(['store', 'plan']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscriptions->items(),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    /**
     * Manually activate subscription (admin)
     * @route PUT /api/v1/admin/subscriptions/{id}/activate
     */
    public function adminActivate(Request $request, string $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $subscription->billing_cycle === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل الاشتراك',
            'data' => $subscription->fresh(),
        ]);
    }

    /**
     * Manage plans (admin CRUD)
     * @route POST /api/v1/admin/plans
     */
    public function adminCreatePlan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'products_limit' => 'required|integer|min:-1',
            'orders_limit' => 'required|integer|min:-1',
            'storage_limit_mb' => 'required|integer|min:-1',
            'staff_limit' => 'required|integer|min:0',
            'domains_limit' => 'required|integer|min:0',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = SubscriptionPlan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الخطة',
            'data' => $plan,
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function getPaymentInstructions(string $method, float $amount): array
    {
        return match($method) {
            'cib' => [
                'type' => 'card',
                'title' => 'الدفع بالبطاقة البنكية CIB',
                'description' => "يرجى تحويل مبلغ {$amount} دج عبر البطاقة البنكية CIB",
                'redirect_url' => '/payment/cib',
            ],
            'edahabia' => [
                'type' => 'card',
                'title' => 'الدفع ببطاقة الذهبية',
                'description' => "يرجى تحويل مبلغ {$amount} دج عبر بطاقة الذهبية",
                'redirect_url' => '/payment/edahabia',
            ],
            'transfer' => [
                'type' => 'bank_transfer',
                'title' => 'التحويل البنكي',
                'description' => "يرجى تحويل مبلغ {$amount} دج إلى الحساب البنكي التالي",
                'bank_name' => 'بريد الجزائر',
                'account_number' => 'XXXXXXXXXX',
                'rib' => 'XXXXXXXXXX',
            ],
            'cash' => [
                'type' => 'cash',
                'title' => 'الدفع نقداً',
                'description' => 'تواصل معنا لترتيب الدفع النقدي',
                'phone' => '+213XXXXXXXXX',
            ],
            default => [
                'type' => 'contact',
                'title' => 'تواصل معنا',
                'description' => 'تواصل معنا لإتمام عملية الدفع',
            ],
        };
    }
}
