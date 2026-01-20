<?php

namespace Ingenius\Auth\Traits;

use Ingenius\Auth\Notifications\TenantResetPassword;

/**
 * Trait to add password reset support to tenant user models.
 *
 * Usage:
 * 1. Add this trait to your User model
 * 2. Implement Illuminate\Contracts\Auth\CanResetPassword interface
 *
 * Example:
 * class User extends Authenticatable implements CanResetPassword
 * {
 *     use CanResetPasswordForTenant;
 * }
 */
trait CanResetPasswordForTenant
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @param  string  $source  The source of the request (store or backoffice)
     * @return void
     */
    public function sendPasswordResetNotification($token, string $source = 'store')
    {
        // Prevent duplicate emails in the same request with tenant-scoped cache key
        $tenant = tenant();
        $tenantId = $tenant ? $tenant->id : 'central';
        $cacheKey = "password_reset_sent_{$tenantId}_{$this->getKey()}";

        if (cache()->has($cacheKey)) {
            return;
        }

        $this->notify(new TenantResetPassword($token, $source));

        // Cache for 5 seconds to prevent duplicates in same request cycle
        cache()->put($cacheKey, true, 5);
    }
}
