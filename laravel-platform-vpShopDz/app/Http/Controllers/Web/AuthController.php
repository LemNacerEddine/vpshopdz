<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'store_name' => 'required|string|max:255',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'store_name.required' => 'اسم المتجر مطلوب',
        ]);

        // Generate unique slug
        $slug = Str::slug($request->store_name);
        $originalSlug = $slug;
        $counter = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Get trial plan
        $trialPlan = SubscriptionPlan::where('price_monthly', 0)->first();

        // Create store
        $store = Store::create([
            'name' => $request->store_name,
            'name_ar' => $request->store_name,
            'slug' => $slug,
            'subdomain' => $slug,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays($trialPlan?->trial_days ?? 7),
            'currency' => 'DZD',
            'language' => 'ar',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'store_owner',
            'store_id' => $store->id,
        ]);

        // Update store owner
        $store->update(['owner_id' => $user->id]);

        // Create trial subscription
        if ($trialPlan) {
            $store->subscriptions()->create([
                'plan_id' => $trialPlan->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'ends_at' => now()->addDays($trialPlan->trial_days),
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'تم إنشاء حسابك ومتجرك بنجاح! 🎉');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
