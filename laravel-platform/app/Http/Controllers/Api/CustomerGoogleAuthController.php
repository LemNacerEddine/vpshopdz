<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CustomerGoogleAuthController extends Controller
{
    /**
     * Return the Google OAuth redirect URL for a given store.
     * GET /api/v1/store/{store}/customer/auth/google
     */
    public function redirect(Request $request, Store $store)
    {
        // Store the store slug in the OAuth state so we can retrieve it in the callback
        $state = base64_encode(json_encode([
            'store' => $store->slug,
            'nonce' => Str::random(16),
        ]));

        $authUrl = Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

        return response()->json(['authUrl' => $authUrl]);
    }

    /**
     * Handle Google OAuth callback (redirect from Google).
     * GET /api/v1/auth/google/callback
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return $this->failRedirect('فشل التحقق من Google');
        }

        // Decode state to find store slug
        $stateRaw = $request->get('state');
        $storeSlug = null;

        if ($stateRaw) {
            $decoded = json_decode(base64_decode($stateRaw), true);
            $storeSlug = $decoded['store'] ?? null;
        }

        if (!$storeSlug) {
            return $this->failRedirect('بيانات المتجر مفقودة');
        }

        $store = Store::where('slug', $storeSlug)->first();
        if (!$store) {
            return $this->failRedirect('المتجر غير موجود');
        }

        // Find or create customer
        $customer = Customer::where('store_id', $store->id)
            ->where('google_id', $googleUser->getId())
            ->first();

        if (!$customer) {
            // Try to find by email
            $customer = Customer::where('store_id', $store->id)
                ->where('email', $googleUser->getEmail())
                ->first();

            if ($customer) {
                $customer->update(['google_id' => $googleUser->getId()]);
            } else {
                // Create new customer
                $customer = Customer::create([
                    'store_id'   => $store->id,
                    'name'       => $googleUser->getName() ?? $googleUser->getEmail(),
                    'email'      => $googleUser->getEmail(),
                    'google_id'  => $googleUser->getId(),
                    'avatar'     => $googleUser->getAvatar(),
                    'password'   => null,
                ]);
            }
        }

        // Create a short-lived session token stored in cache
        $sessionId = Str::random(64);
        Cache::put(
            "google_session_{$sessionId}",
            ['customer_id' => $customer->id, 'store_id' => $store->id],
            now()->addMinutes(5)
        );

        // Redirect back to storefront callback page
        $frontendUrl = rtrim($store->domain ?: config('app.url'), '/');
        return redirect("{$frontendUrl}/auth/callback#session_id={$sessionId}");
    }

    /**
     * Exchange a short-lived session_id for a Sanctum token.
     * POST /api/v1/store/{store}/customer/auth/google/session
     */
    public function session(Request $request, Store $store)
    {
        $request->validate(['session_id' => 'required|string']);

        $data = Cache::pull("google_session_{$request->session_id}");

        if (!$data || $data['store_id'] !== $store->id) {
            return response()->json(['message' => 'جلسة غير صالحة أو منتهية'], 401);
        }

        $customer = Customer::find($data['customer_id']);
        if (!$customer) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $customer->tokens()->delete();
        $token = $customer->createToken('storefront-google-' . $store->slug)->plainTextToken;

        return response()->json([
            'customer' => $customer->makeVisible(['email'])->toArray(),
            'token'    => $token,
            'message'  => 'تم تسجيل الدخول بنجاح',
        ]);
    }

    private function failRedirect(string $message): \Illuminate\Http\RedirectResponse
    {
        $url = config('app.url');
        return redirect("{$url}/login?error=" . urlencode($message));
    }
}
