<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Check if the authenticated user's store has an active subscription.
     * Used for dashboard routes.
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 401);
        }

        $store = $user->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد متجر مرتبط بحسابك',
            ], 404);
        }

        // Check if store has active subscription
        if (!$store->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'اشتراكك منتهي. يرجى تجديد الاشتراك.',
                'subscription_expired' => true,
            ], 403);
        }

        // Check specific features if provided
        foreach ($features as $feature) {
            if (!$store->canUseFeature($feature)) {
                return response()->json([
                    'success' => false,
                    'message' => "هذه الميزة ({$feature}) غير متوفرة في خطتك الحالية",
                    'upgrade_required' => true,
                    'feature' => $feature,
                ], 403);
            }
        }

        return $next($request);
    }
}
