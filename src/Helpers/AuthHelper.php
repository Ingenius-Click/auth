<?php

namespace Ingenius\Auth\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * Helper for authentication across different guards
 *
 * Example usage:
 *
 * ```php
 * use Ingenius\Auth\Helpers\AuthHelper;
 *
 * // Get authenticated user from any guard
 * $user = AuthHelper::getUser();
 *
 * // Check if user is authenticated in any guard
 * if (AuthHelper::check()) {
 *    // User is authenticated
 * }
 *
 * // Get user from a specific guard
 * $tenantUser = AuthHelper::getUserFromGuard('tenant');
 * $sanctumUser = AuthHelper::getUserFromGuard('sanctum');
 * ```
 */
class AuthHelper
{
    /**
     * Get authenticated user from sanctum or tenant guard
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getUser()
    {
        // Try to get user from sanctum guard first
        $user = Auth::guard('sanctum')->user();


        // If no user found in sanctum guard, try tenant guard
        if (!$user) {
            $user = Auth::guard('tenant')->user();
        }

        return $user;
    }

    /**
     * Check if user is authenticated in any of the guards
     *
     * @return bool
     */
    public static function check()
    {
        return Auth::guard('sanctum')->check() || Auth::guard('tenant')->check();
    }

    /**
     * Get user from a specific guard
     *
     * @param string $guard
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getUserFromGuard(string $guard)
    {
        return Auth::guard($guard)->user();
    }

    public static function checkPermission(string $permission) {
        $user = AuthHelper::getUser();
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            if(!$user->can($permission)) {
                abort(403);
            }
        } else {
            abort(403);
        }
    }
}
