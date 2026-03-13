<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    /**
     * Get store staff members
     * @route GET /api/v1/dashboard/staff
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $staff = User::where('store_id', $store->id)
            ->where('role', 'staff')
            ->get(['id', 'name', 'email', 'phone', 'permissions', 'is_active', 'last_login_at', 'created_at']);

        $invitations = StaffInvitation::where('store_id', $store->id)
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => $staff,
                'pending_invitations' => $invitations,
                'limit' => $store->plan->staff_limit ?? 0,
                'current_count' => $staff->count(),
            ],
        ]);
    }

    /**
     * Invite staff member
     * @route POST /api/v1/dashboard/staff/invite
     */
    public function invite(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check staff limit
        $plan = $store->plan;
        $limit = $plan->staff_limit ?? 0;
        $current = User::where('store_id', $store->id)->where('role', 'staff')->count();

        if ($limit > 0 && $current >= $limit) {
            return response()->json([
                'success' => false,
                'message' => "وصلت للحد الأقصى من أعضاء الفريق ({$limit})",
                'upgrade_required' => true,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:products,orders,customers,analytics,settings,shipping,coupons,pages,marketing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if already invited or exists
        $existingInvite = StaffInvitation::where('store_id', $store->id)
            ->where('email', $request->email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return response()->json([
                'success' => false,
                'message' => 'تم إرسال دعوة لهذا البريد بالفعل',
            ], 400);
        }

        $invitation = StaffInvitation::create([
            'store_id' => $store->id,
            'email' => $request->email,
            'name' => $request->name,
            'token' => Str::random(64),
            'permissions' => $request->permissions ?? ['orders', 'products'],
            'status' => 'pending',
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        // TODO: Send invitation email

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الدعوة',
            'data' => $invitation,
        ], 201);
    }

    /**
     * Accept invitation (public route)
     * @route POST /api/v1/staff/accept-invitation
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $invitation = StaffInvitation::where('token', $request->token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'الدعوة غير صالحة أو منتهية الصلاحية',
            ], 404);
        }

        // Create user or update existing
        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            $user->update([
                'store_id' => $invitation->store_id,
                'role' => 'staff',
                'permissions' => $invitation->permissions,
            ]);
        } else {
            $user = User::create([
                'name' => $invitation->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'store_id' => $invitation->store_id,
                'role' => 'staff',
                'permissions' => $invitation->permissions,
                'is_active' => true,
            ]);
        }

        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول الدعوة بنجاح',
        ]);
    }

    /**
     * Update staff permissions
     * @route PUT /api/v1/dashboard/staff/{id}/permissions
     */
    public function updatePermissions(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $staff = User::where('store_id', $store->id)
            ->where('id', $id)
            ->where('role', 'staff')
            ->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'عضو الفريق غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string|in:products,orders,customers,analytics,settings,shipping,coupons,pages,marketing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $staff->update(['permissions' => $request->permissions]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصلاحيات',
        ]);
    }

    /**
     * Remove staff member
     * @route DELETE /api/v1/dashboard/staff/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $staff = User::where('store_id', $store->id)
            ->where('id', $id)
            ->where('role', 'staff')
            ->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'عضو الفريق غير موجود'], 404);
        }

        // Don't delete, just remove from store
        $staff->update([
            'store_id' => null,
            'role' => 'merchant',
            'permissions' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة عضو الفريق',
        ]);
    }

    /**
     * Cancel invitation
     * @route DELETE /api/v1/dashboard/staff/invitations/{id}
     */
    public function cancelInvitation(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $invitation = StaffInvitation::where('store_id', $store->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json(['success' => false, 'message' => 'الدعوة غير موجودة'], 404);
        }

        $invitation->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الدعوة',
        ]);
    }
}
