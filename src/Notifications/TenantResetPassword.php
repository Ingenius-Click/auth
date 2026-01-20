<?php

namespace Ingenius\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Ingenius\Auth\Settings\AuthSettings;

class TenantResetPassword extends BaseResetPassword
{
    /**
     * The source of the password reset request (store or backoffice).
     * Used to determine which redirect URL to use.
     */
    protected string $source;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, string $source = 'store')
    {
        parent::__construct($token);
        $this->source = $source;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);
        $tenant = tenant();
        $tenantName = $tenant?->getName() ?? config('app.name');
        $expireMinutes = config('auth.passwords.tenant_users.expire', 60);

        return (new MailMessage)
            ->subject(__('auth::passwords.subject') . ' - ' . $tenantName)
            ->greeting(__('auth::passwords.greeting', ['name' => $notifiable->name]))
            ->line(__('auth::passwords.line_1'))
            ->action(__('auth::passwords.action'), $resetUrl)
            ->line(__('auth::passwords.line_2', ['minutes' => $expireMinutes]))
            ->line(__('auth::passwords.line_3'))
            ->salutation(__('auth::passwords.salutation') . ', ' . $tenantName);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        $tenant = tenant();

        if (!$tenant) {
            throw new \Exception('Tenant context not initialized when sending password reset email');
        }

        // Get the first domain for this tenant
        $domain = $tenant->domains()->first();

        if (!$domain) {
            throw new \Exception('No domain found for tenant: ' . $tenant->id);
        }

        $tenantDomain = $domain->domain;

        // Build the full tenant URL with proper protocol and port
        $protocol = config('app.env') === 'local' || request()->getScheme() === 'http' ? 'http' : 'https';

        // Get port from current request if available
        $port = '';
        if (request()->getPort()) {
            $defaultPort = $protocol === 'https' ? 443 : 80;
            if (request()->getPort() != $defaultPort) {
                $port = ':' . request()->getPort();
            }
        }

        // Get the frontend reset URL from tenant settings based on source
        $authSettings = AuthSettings::make();

        if ($this->source === 'backoffice') {
            $frontendResetUrl = $authSettings->backoffice_password_reset_redirect_url;
        } else {
            $frontendResetUrl = $authSettings->password_reset_redirect_url;
        }

        // If a frontend URL is configured, use it with query parameters
        if (!empty($frontendResetUrl)) {
            $separator = str_contains($frontendResetUrl, '?') ? '&' : '?';
            return $frontendResetUrl . $separator . http_build_query([
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        }

        // Fallback: build URL with tenant domain
        $fullTenantUrl = "{$protocol}://{$tenantDomain}{$port}";

        return $fullTenantUrl . '/password/reset?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
