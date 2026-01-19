<?php

namespace Ingenius\Auth\Traits;

use Ingenius\Auth\Notifications\TenantVerifyEmail;

/**
 * Trait to add email verification support to tenant user models.
 *
 * Usage:
 * 1. Add this trait to your User model
 * 2. Implement Illuminate\Contracts\Auth\MustVerifyEmail interface
 *
 * Example:
 * class User extends Authenticatable implements MustVerifyEmail
 * {
 *     use MustVerifyEmailForTenant;
 * }
 */
trait MustVerifyEmailForTenant
{
    /**
     * Send the email verification notification.
     *
     * @param  string  $source  The source of the request (store or backoffice)
     * @return void
     */
    public function sendEmailVerificationNotification(string $source = 'store')
    {
        // Prevent duplicate emails in the same request with tenant-scoped cache key
        $tenant = tenant();
        $tenantId = $tenant ? $tenant->id : 'central';
        $cacheKey = "verification_sent_{$tenantId}_{$this->getKey()}";

        if (cache()->has($cacheKey)) {
            return;
        }

        $this->notify(new TenantVerifyEmail($source));

        // Cache for 5 seconds to prevent duplicates in same request cycle
        cache()->put($cacheKey, true, 5);
    }
}
