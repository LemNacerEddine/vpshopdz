<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreDomain;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
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
                ->where('is_active', true)
                ->first();
        }
        // Check if it's a custom domain
        elseif ($host !== $platformDomain && $host !== 'www.' . $platformDomain) {
            $domain = StoreDomain::where('domain', $host)
                ->where('is_verified', true)
                ->first();

            if ($domain) {
                $store = Store::where('id', $domain->store_id)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$store) {
            abort(404, 'المتجر غير موجود');
        }

        // Check subscription status
        $subscription = $store->activeSubscription();
        if (!$subscription && $store->user?->role !== 'admin') {
            abort(403, 'الاشتراك غير فعال');
        }

        // Get active theme
        $storeTheme = $store->activeTheme;
        $theme = $storeTheme?->theme;

        $themeSettings = [
            'colors' => $storeTheme?->custom_colors ?? ($theme?->default_colors ?? []),
            'fonts' => $storeTheme?->custom_fonts ?? ($theme?->default_fonts ?? []),
            'layout' => $storeTheme?->custom_layout ?? ($theme?->default_layout ?? []),
        ];

        // Pass store data to React storefront via Blade template
        return view('storefront', [
            'store' => $store,
            'theme' => $theme,
            'themeSettings' => $themeSettings,
        ]);
    }
}
