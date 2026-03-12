<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStore
{
    /**
     * Resolve the store from the request (subdomain or custom domain).
     * This middleware is used for storefront routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = null;
        $host = $request->getHost();
        $platformDomain = config('app.domain', 'vpshopdz.com');

        // 1. Check if it's a subdomain (e.g., mystore.vpshopdz.com)
        if (str_ends_with($host, '.' . $platformDomain)) {
            $subdomain = str_replace('.' . $platformDomain, '', $host);

            if ($subdomain && $subdomain !== 'www' && $subdomain !== 'api') {
                $store = Store::where('slug', $subdomain)
                    ->where('status', 'active')
                    ->first();
            }
        }

        // 2. Check if it's a custom domain
        if (!$store && $host !== $platformDomain && !str_ends_with($host, '.' . $platformDomain)) {
            $domain = StoreDomain::where('domain', $host)
                ->where('is_verified', true)
                ->first();

            if ($domain) {
                $store = Store::where('id', $domain->store_id)
                    ->where('status', 'active')
                    ->first();
            }
        }

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'المتجر غير موجود أو غير نشط',
            ], 404);
        }

        // Check subscription
        if (!$store->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'اشتراك المتجر منتهي',
            ], 403);
        }

        // Share store with the request
        $request->merge(['resolved_store' => $store]);
        app()->instance('current_store', $store);

        return $next($request);
    }
}
