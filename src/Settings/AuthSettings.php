<?php

namespace Ingenius\Auth\Settings;

use Ingenius\Core\Settings\Settings;

class AuthSettings extends Settings
{
    /**
     * URL to redirect users after email verification.
     * This should be a full URL including the protocol (e.g., https://example.com/dashboard).
     *
     * @var string
     */
    public string $email_verification_redirect_url = '';

    public string $backoffice_email_verification_redirect_url = '';

    /**
     * URL to redirect users after successful password reset.
     * This should be a full URL including the protocol (e.g., https://example.com/login).
     *
     * @var string
     */
    public string $password_reset_redirect_url = '';

    /**
     * Get the group name for these settings.
     *
     * @return string
     */
    public static function group(): string
    {
        return 'auth';
    }
}
