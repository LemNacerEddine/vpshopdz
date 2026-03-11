<?php

namespace App\Http\Middleware;

use App\Models\BrowsingHistory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Track visitor browsing for analytics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests
        if ($request->method() !== 'GET') {
            return $response;
        }

        $store = app()->bound('current_store') ? app('current_store') : null;

        if (!$store) {
            return $response;
        }

        // Don't track if store doesn't have advanced analytics
        if (!$store->canUseFeature('advanced_analytics')) {
            return $response;
        }

        try {
            // Generate session ID for anonymous visitors
            $sessionId = $request->cookie('visitor_session') ?? md5($request->ip() . $request->userAgent());

            BrowsingHistory::create([
                'store_id' => $store->id,
                'session_id' => $sessionId,
                'page_url' => $request->fullUrl(),
                'page_type' => $this->detectPageType($request),
                'product_id' => $request->route('product'),
                'category_id' => $request->route('category'),
                'referrer' => $request->header('referer'),
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->detectDeviceType($request->userAgent()),
                'country' => null, // TODO: GeoIP lookup
            ]);
        } catch (\Exception $e) {
            // Don't fail the request if tracking fails
            \Log::warning('Visitor tracking failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function detectPageType(Request $request): string
    {
        $path = $request->path();

        if ($path === '/' || $path === '') return 'home';
        if (str_contains($path, 'product/')) return 'product';
        if (str_contains($path, 'category/')) return 'category';
        if (str_contains($path, 'cart')) return 'cart';
        if (str_contains($path, 'checkout')) return 'checkout';
        if (str_contains($path, 'search')) return 'search';
        if (str_contains($path, 'page/')) return 'page';

        return 'other';
    }

    private function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }
}
