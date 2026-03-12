<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer for a store
     */
    public function register(Request $request, Store $store)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'email'    => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
            'wilaya'   => 'nullable|string',
            'commune'  => 'nullable|string',
            'address'  => 'nullable|string',
        ]);

        // Check phone uniqueness per store
        if (Customer::where('store_id', $store->id)->where('phone', $request->phone)->exists()) {
            return response()->json(['message' => 'رقم الهاتف مستخدم مسبقاً في هذا المتجر'], 422);
        }

        // Check email uniqueness per store (if provided)
        if ($request->email && Customer::where('store_id', $store->id)->where('email', $request->email)->exists()) {
            return response()->json(['message' => 'البريد الإلكتروني مستخدم مسبقاً'], 422);
        }

        $customer = Customer::create([
            'store_id' => $store->id,
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'wilaya'   => $request->wilaya,
            'commune'  => $request->commune,
            'address'  => $request->address,
        ]);

        $token = $customer->createToken('storefront-' . $store->slug)->plainTextToken;

        return response()->json([
            'customer' => $customer->makeVisible(['email'])->toArray(),
            'token'    => $token,
            'message'  => 'تم إنشاء الحساب بنجاح',
        ], 201);
    }

    /**
     * Login customer
     */
    public function login(Request $request, Store $store)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = $request->identifier;

        // Find by phone or email within this store
        $customer = Customer::where('store_id', $store->id)
            ->where(function ($q) use ($identifier) {
                $q->where('phone', $identifier)->orWhere('email', $identifier);
            })
            ->first();

        if (!$customer || !$customer->password) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        if (!Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // Revoke old tokens and create new one
        $customer->tokens()->delete();
        $token = $customer->createToken('storefront-' . $store->slug)->plainTextToken;

        return response()->json([
            'customer' => $customer->makeVisible(['email'])->toArray(),
            'token'    => $token,
            'message'  => 'تم تسجيل الدخول بنجاح',
        ]);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    /**
     * Get customer profile
     */
    public function profile(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'data' => $customer->makeVisible(['email'])->load('addresses'),
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|nullable|email|max:255',
            'phone'   => 'sometimes|string|max:20',
            'wilaya'  => 'sometimes|nullable|string',
            'commune' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
        ]);

        // Check phone uniqueness per store (if changing)
        if ($request->has('phone') && $request->phone !== $customer->phone) {
            if (Customer::where('store_id', $customer->store_id)->where('phone', $request->phone)->where('id', '!=', $customer->id)->exists()) {
                return response()->json(['message' => 'رقم الهاتف مستخدم مسبقاً'], 422);
            }
        }

        $customer->update($request->only(['name', 'email', 'phone', 'wilaya', 'commune', 'address']));

        return response()->json([
            'data'    => $customer->fresh()->makeVisible(['email']),
            'message' => 'تم تحديث الملف الشخصي بنجاح',
        ]);
    }

    /**
     * Change customer password
     */
    public function changePassword(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $customer->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    /**
     * Get customer orders
     */
    public function myOrders(Request $request)
    {
        $customer = $request->user();

        $orders = $customer->orders()
            ->with(['items' => function ($q) { $q->with('product:id,name,name_ar,name_fr,images'); }])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($orders);
    }
}
