<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreDomain;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Serve storefront by store slug (for local dev: /store/{slug})
     */
    public function bySlug(Request $request, string $slug)
    {
        $store = Store::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!$store) {
            abort(404, 'المتجر غير موجود أو غير نشط');
        }

        return $this->serveStorefront($store);
    }

    /**
     * Resolve store from subdomain or custom domain and serve React storefront
     */
    public function index(Request $request)
    {
        $host = $request->getHost();
        $platformDomain = config('platform.domain', 'vpshopdz.com');

        $store = null;

        // Check if it's a subdomain (e.g., mystore.vpshopdz.com)
        if (str_ends_with($host, '.' . $platformDomain)) {
            $subdomain = str_replace('.' . $platformDomain, '', $host);

            // Skip www
            if ($subdomain === 'www') {
                return redirect()->to(config('app.url'));
            }

            $store = Store::where('slug', $subdomain)
                ->where('status', 'active')
                ->first();
        }
        // Check if it's a custom domain
        elseif ($host !== $platformDomain && $host !== 'www.' . $platformDomain) {
            // Try to find by custom domain
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
            // If no store found via subdomain/domain, show platform landing page
            return view('welcome');
        }

        return $this->serveStorefront($store);
    }

    /**
     * Serve the React storefront view with store data
     */
    private function serveStorefront(Store $store)
    {
        // Check subscription status (allow demo/free stores)
        $subscription = null;
        try {
            $subscription = $store->activeSubscription();
        } catch (\Exception $e) {
            // Subscription check failed, allow access for now
        }

        // Get active theme
        $storeTheme = null;
        $theme = null;
        try {
            $storeTheme = $store->activeTheme;
            $theme = $storeTheme?->theme;
        } catch (\Exception $e) {
            // Theme loading failed, use defaults
        }

        $themeSettings = [
            'colors' => $storeTheme?->custom_colors ?? ($theme?->default_colors ?? []),
            'fonts'  => $storeTheme?->custom_fonts  ?? ($theme?->default_fonts  ?? []),
            'layout' => $storeTheme?->custom_layout  ?? ($theme?->default_layout  ?? []),
        ];

        // Bind current store to app container for middleware use
        app()->instance('current_store', $store);

        // Pass store data to React storefront via Blade template
        return view('storefront', [
            'store'         => $store,
            'theme'         => $theme,
            'themeSettings' => $themeSettings,
        ]);
    }
}
