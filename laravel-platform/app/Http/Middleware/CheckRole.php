<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if the authenticated user has one of the required roles.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تسجيل الدخول',
            ], 401);
        }

        // Check if user is banned
        if ($user->is_banned) {
            return response()->json([
                'success' => false,
                'message' => 'تم حظر حسابك. تواصل مع الدعم.',
            ], 403);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية للوصول إلى هذا المورد',
            ], 403);
        }

        return $next($request);
    }
}
