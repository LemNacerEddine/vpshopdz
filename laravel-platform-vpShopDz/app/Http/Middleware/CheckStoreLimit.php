<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreLimit
{
    /**
     * Check if the store has reached its limits (products, orders, storage).
     */
    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $store = $request->user()->store ?? null;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد متجر',
            ], 404);
        }

        $plan = $store->plan;

        if (!$plan) {
            return $next($request);
        }

        $exceeded = false;
        $message = '';

        switch ($limitType) {
            case 'products':
                $limit = $plan->products_limit;
                if ($limit > 0) {
                    $current = $store->products()->count();
                    if ($current >= $limit) {
                        $exceeded = true;
                        $message = "وصلت للحد الأقصى من المنتجات ({$limit}). يرجى الترقية لإضافة المزيد.";
                    }
                }
                break;

            case 'orders':
                $limit = $plan->orders_limit;
                if ($limit > 0) {
                    $current = $store->ordersThisMonth();
                    if ($current >= $limit) {
                        $exceeded = true;
                        $message = "وصلت للحد الأقصى من الطلبات الشهرية ({$limit}). يرجى الترقية.";
                    }
                }
                break;

            case 'storage':
                $limit = $plan->storage_limit_mb;
                if ($limit > 0) {
                    $current = $store->storageUsedMb();
                    if ($current >= $limit) {
                        $exceeded = true;
                        $message = "وصلت للحد الأقصى من مساحة التخزين ({$limit} ميجابايت). يرجى الترقية.";
                    }
                }
                break;

            case 'staff':
                $limit = $plan->staff_limit;
                if ($limit > 0) {
                    $current = $store->staff()->count();
                    if ($current >= $limit) {
                        $exceeded = true;
                        $message = "وصلت للحد الأقصى من أعضاء الفريق ({$limit}). يرجى الترقية.";
                    }
                }
                break;
        }

        if ($exceeded) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'upgrade_required' => true,
                'limit_type' => $limitType,
            ], 403);
        }

        return $next($request);
    }
}
