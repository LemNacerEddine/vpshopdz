<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreDomain;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Resolve store from subdomain or custom domain and serve storefront
     */
    public function index(Request $request)
    {
        $host = $request->getHost();
        $platformDomain = config('platform.domain', 'vpshopdz.com');

        $store = null;

        // Check if it's a subdomain
        if (str_ends_with($host, '.' . $platformDomain)) {
            $subdomain = str_replace('.' . $platformDomain, '', $host);
            $store = Store::where('slug', $subdomain)
                ->where('status', 'active')
                ->first();
        }
        // Check if it's a custom domain
        else if ($host !== $platformDomain && $host !== 'www.' . $platformDomain) {
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
            abort(404, 'المتجر غير موجود');
        }

        // Get active theme
        $activeTheme = $store->activeTheme();
        $themeSettings = $store->activeThemeSettings();

        // Pass store data to React storefront
        return view('storefront', [
            'store' => $store,
            'theme' => $activeTheme,
            'themeSettings' => $themeSettings,
            'storeJson' => json_encode([
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo' => $store->logo,
                'description' => $store->description,
                'currency' => $store->currency,
                'language' => $store->language,
                'settings' => $store->settings,
                'theme' => $activeTheme ? [
                    'slug' => $activeTheme->slug,
                    'colors' => $themeSettings['colors'] ?? $activeTheme->default_colors,
                    'fonts' => $themeSettings['fonts'] ?? $activeTheme->default_fonts,
                    'layout' => $themeSettings['layout'] ?? $activeTheme->default_layout,
                    'sections' => $activeTheme->sections,
                ] : null,
            ]),
        ]);
    }
}
