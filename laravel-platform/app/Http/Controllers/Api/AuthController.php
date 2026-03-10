<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * تسجيل تاجر جديد
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'store_name' => 'required|string|max:255',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'store_name.required' => 'اسم المتجر مطلوب',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'store_owner',
        ]);

        // Create store
        $slug = Str::slug($request->store_name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Store::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $store = Store::create([
            'owner_id' => $user->id,
            'name' => $request->store_name,
            'slug' => $slug,
            'subdomain' => $slug,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
            'currency' => 'DZD',
            'language' => 'ar',
        ]);

        // Update user with store_id
        $user->update(['store_id' => $store->id]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'url' => $store->getUrl(),
                    'status' => $store->status,
                    'trial_ends_at' => $store->trial_ends_at,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * تسجيل الدخول
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        $store = $user->store;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                ],
                'store' => $store ? [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'logo' => $store->logo,
                    'status' => $store->status,
                ] : null,
                'token' => $token,
            ]
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    /**
     * معلومات المستخدم الحالي
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = $user->store;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'permissions' => $user->permissions,
                ],
                'store' => $store ? [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'logo' => $store->logo,
                    'status' => $store->status,
                    'trial_ends_at' => $store->trial_ends_at,
                    'subscription' => $store->subscription ? [
                        'plan' => $store->subscription->plan->name,
                        'status' => $store->subscription->status,
                        'ends_at' => $store->subscription->ends_at,
                    ] : null,
                ] : null,
            ]
        ]);
    }

    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'phone', 'avatar']));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                ]
            ]
        ]);
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }
}
