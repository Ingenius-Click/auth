<?php

namespace Ingenius\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're in a tenant context
        if (!tenant()) {
            abort(404, 'Not found');
        }

        // For API requests (token authentication)
        if ($request->bearerToken()) {
            // When using a bearer token, Sanctum will handle authentication
            // We just need to check if the user is authenticated at all
            if (!$request->user('sanctum')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }
        // For web requests (session authentication)
        else if (!Auth::guard('tenant')->check()) {
            Log::info('Session ID: ' . session()->getId());
            // Redirect to login if not authenticated
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            abort(401, 'Unauthorized');
        }

        // Any additional tenant-specific validation could go here
        // For example, checking for tenant-specific roles/permissions

        return $next($request);
    }
}
