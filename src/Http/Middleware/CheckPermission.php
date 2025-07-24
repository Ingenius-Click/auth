<?php

namespace Ingenius\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if we're in a tenant context
        if (!tenant()) {
            abort(404, 'Not found');
        }

        // Get the appropriate user based on context
        $user = null;

        // For API requests (token authentication)
        if ($request->bearerToken()) {
            $user = $request->user('sanctum');
        }
        // For web requests (session authentication)
        else {
            $user = Auth::guard('tenant')->user();
        }

        // Check if the user is authenticated
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            abort(401, 'Unauthorized');
        }

        // Check if the user has the required permission
        if (!$user->hasPermissionTo($permission)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden - You do not have the required permission: ' . $permission], 403);
            }
            abort(403, 'Forbidden - You do not have the required permission: ' . $permission);
        }

        return $next($request);
    }
}
