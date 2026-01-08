<?php

namespace Ingenius\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class TenantVerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $tenant = tenant();

        if (!$tenant) {
            throw new \Exception('Tenant context not initialized when sending verification email');
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

        $fullTenantUrl = "{$protocol}://{$tenantDomain}{$port}";

        // Temporarily override APP_URL to match tenant domain for signature generation
        $originalUrl = config('app.url');
        config(['app.url' => $fullTenantUrl]);

        // Generate the signed URL with correct domain in signature
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Restore original APP_URL
        config(['app.url' => $originalUrl]);

        // Add tenant query parameter for reliable tenant identification
        $separator = str_contains($verificationUrl, '?') ? '&' : '?';
        $verificationUrl .= $separator . 'tenant=' . urlencode($tenantDomain);

        return $verificationUrl;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $tenant = tenant();
        $tenantName = $tenant?->getName() ?? config('app.name');
        $expireMinutes = config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject(__('auth::verification.subject') . ' - ' . $tenantName)
            ->greeting(__('auth::verification.greeting', ['name' => $notifiable->name]))
            ->line(__('auth::verification.line_1'))
            ->action(__('auth::verification.action'), $verificationUrl)
            ->line(__('auth::verification.line_2', ['minutes' => $expireMinutes]))
            ->line(__('auth::verification.line_3'))
            ->salutation(__('auth::verification.salutation') . ', ' . $tenantName);
    }
}
