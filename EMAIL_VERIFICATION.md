# Email Verification Documentation

This guide explains how email verification works in the multi-tenancy system and how to set it up for custom User models.

## 📋 Table of Contents

1. [Overview](#overview)
2. [How It Works](#how-it-works)
3. [Default Configuration](#default-configuration)
4. [Using Custom User Models](#using-custom-user-models)
5. [Verification Command](#verification-command)
6. [Routes](#routes)
7. [Multi-Tenancy Considerations](#multi-tenancy-considerations)
8. [Testing](#testing)

---

## Overview

Email verification ensures that users verify their email address before accessing protected features. The system supports:

- ✅ **Tenant-specific verification** with query parameter-based tenant identification
- ✅ **Central app verification** for admin/central users
- ✅ **Custom User models** via configuration
- ✅ **Automatic verification email** on registration
- ✅ **Login blocking** for unverified users

---

## How It Works

### Tenant User Registration Flow

1. User registers via `POST /api/register`
2. System creates user account (unverified)
3. `Registered` event triggers email verification notification
4. User receives email with verification link containing:
   - Signed URL with user ID and hash
   - **`?tenant={domain}` query parameter** for tenant identification
5. User clicks link from email client
6. Middleware initializes tenancy from `?tenant=` parameter
7. System verifies signature and marks email as verified
8. User can now log in

### Why Query Parameter?

The verification link includes `?tenant={domain}` because:
- Email links are clicked from **outside the application** (Gmail, Outlook, etc.)
- The domain in the URL might not match the tenant's primary domain
- The query parameter ensures reliable tenant identification
- Middleware ([InitializeTenancyByDomain.php:24](packages/ingenius/core/src/Http/Middleware/InitializeTenancyByDomain.php#L24)) reads it as fallback

**Example verification URL:**
```
https://tenant1.example.com/api/email/verify/123/abc123def456?tenant=tenant1.example.com&expires=...&signature=...
```

---

## Default Configuration

### Tenant Users

The default tenant User model ([packages/ingenius/auth/src/Models/User.php](packages/ingenius/auth/src/Models/User.php)) already implements email verification:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Ingenius\Auth\Traits\MustVerifyEmailForTenant;

class User extends Authenticatable implements HasCustomerProfile, MustVerifyEmail
{
    use MustVerifyEmailForTenant;
    // ...
}
```

### Central Users

The default central User model ([packages/ingenius/core/src/Models/User.php](packages/ingenius/core/src/Models/User.php)) also implements email verification:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Ingenius\Core\Traits\MustVerifyEmailForCentral;

class User extends Authenticatable implements HasCustomerProfile, MustVerifyEmail
{
    use MustVerifyEmailForCentral;
    // ...
}
```

---

## Using Custom User Models

If you're using a custom User model, follow these steps:

### Step 1: Configure Your User Model

In `config/core.php`:

```php
return [
    'tenant_user_model' => 'App\\Models\\TenantUser', // Your custom model
    // or
    'central_user_model' => 'App\\Models\\CentralUser',
];
```

### Step 2: Implement Email Verification

Add to your custom User model:

**For Tenant Users:**
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ingenius\Auth\Traits\MustVerifyEmailForTenant;

class TenantUser extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyEmailForTenant;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at', // Important!
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

**For Central Users:**
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ingenius\Core\Traits\MustVerifyEmailForCentral;

class CentralUser extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyEmailForCentral;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at', // Important!
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### Step 3: Ensure Database Column Exists

Your users table migration must include:

```php
$table->timestamp('email_verified_at')->nullable();
```

---

## Verification Command

Use the Artisan command to verify your setup:

```bash
# Check tenant user email verification setup
php artisan auth:setup-email-verification

# Check central user email verification setup
php artisan auth:setup-email-verification --central

# Check both
php artisan auth:setup-email-verification --tenant --central

# Only check (don't offer to fix)
php artisan auth:setup-email-verification --check
```

The command will verify:
- ✅ User class exists
- ✅ Implements `MustVerifyEmail` interface
- ✅ Uses correct trait (`MustVerifyEmailForTenant` or `MustVerifyEmailForCentral`)
- ✅ Has `email_verified_at` column

---

## Routes

### Tenant Routes

Email verification routes are automatically registered in [packages/ingenius/auth/routes/tenant.php](packages/ingenius/auth/routes/tenant.php):

| Route | Method | Middleware | Description |
|-------|--------|-----------|-------------|
| `/api/email/verify` | GET | `tenant.user` | Verification notice page |
| `/api/email/verify/{id}/{hash}` | GET | `signed` | Verification handler |
| `/api/email/verification-notification` | POST | `tenant.user`, `throttle:6,1` | Resend verification email |

### Protected Routes Example

To require email verification on routes, use the `verified` middleware:

```php
Route::middleware(['tenant.user', 'verified'])->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
    // ... other protected routes
});
```

Routes that don't require verification (e.g., profile access):

```php
Route::middleware(['tenant.user'])->group(function () {
    Route::get('/api/user', [TenantAuthController::class, 'user']);
    Route::post('/api/logout', [TenantAuthController::class, 'logout']);
});
```

---

## Multi-Tenancy Considerations

### How Tenancy is Initialized

When a user clicks the verification link:

1. **URL structure**: `https://{domain}/api/email/verify/{id}/{hash}?tenant={domain}&...`
2. **Middleware flow** ([InitializeTenancyByDomain.php](packages/ingenius/core/src/Http/Middleware/InitializeTenancyByDomain.php)):
   - Checks `X-Forwarded-Host` header (from proxies)
   - Falls back to `?tenant=` query parameter
   - Falls back to request host
3. **Database lookup**: Finds tenant by domain
4. **Context switch**: Switches to tenant database
5. **Verification**: Validates signature and marks email as verified

### Email Configuration

#### Option 1: Centralized Email Service

Use a single email service for all tenants:

```php
// .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

Emails will be sent from the same address but include tenant-specific branding in the body.

#### Option 2: Tenant-Specific Email Configuration

Configure email settings per tenant (requires additional implementation):

```php
// In TenantVerifyEmail notification
config([
    'mail.from.address' => tenant()->email_from ?? config('mail.from.address'),
    'mail.from.name' => tenant()->name ?? config('mail.from.name'),
]);
```

### Admin Users Auto-Verification

Admin users created during tenant setup are automatically verified:

```php
// packages/ingenius/core/src/Initializers/AuthTenantInitializer.php
$user = tenant_user_class()::create([
    'name' => $name,
    'email' => $email,
    'password' => Hash::make($password),
    'email_verified_at' => now(), // Auto-verify
]);
```

---

## Testing

### Manual Testing

1. **Register a new user:**
```bash
curl -X POST https://tenant1.example.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Expected response:
```json
{
  "status": "success",
  "message": "Registration successful! Please check your email to verify your account before logging in.",
  "data": {
    "email": "test@example.com",
    "email_verified": false
  }
}
```

2. **Try to login (should fail):**
```bash
curl -X POST https://tenant1.example.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

Expected response:
```json
{
  "status": "error",
  "message": "Your email address is not verified. Please check your email for a verification link.",
  "data": {
    "email_verified": false
  }
}
```

3. **Click verification link from email** (or simulate):
```bash
# Get the signed URL from the email, then:
curl https://tenant1.example.com/api/email/verify/1/abc123?tenant=tenant1.example.com&expires=...&signature=...
```

4. **Login again (should succeed):**
```bash
curl -X POST https://tenant1.example.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Automated Testing

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_login_without_verified_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'data' => ['email_verified' => false],
        ]);
    }

    public function test_user_can_verify_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
```

---

## Troubleshooting

### Verification Email Not Sent

**Check:**
1. User model implements `MustVerifyEmail`
2. `Registered` event is triggered in registration controller
3. Mail configuration is correct
4. Queue is processing (if using queued notifications)

**Debug:**
```bash
php artisan tinker
>>> event(new Illuminate\Auth\Events\Registered(User::first()));
```

### Verification Link Invalid

**Common causes:**
1. **App key changed** - signature won't match
2. **Link expired** - default 60 minutes
3. **Wrong tenant context** - ensure `?tenant=` parameter is correct

**Check signature:**
```php
// In routes
Route::get('/debug-verify/{id}/{hash}', function ($id, $hash) {
    dd([
        'id' => $id,
        'hash' => $hash,
        'expected_hash' => sha1(User::find($id)->email),
        'signature_valid' => request()->hasValidSignature(),
    ]);
});
```

### Tenant Not Found

**Error:** "No domain found for tenant"

**Fix:**
Ensure tenant has a domain record:
```bash
php artisan tinker
>>> $tenant = \Ingenius\Core\Models\Tenant::first();
>>> $tenant->domains()->create(['domain' => 'tenant1.example.com']);
```

---

## Configuration Reference

### Environment Variables

```env
# Email verification link expiration (minutes)
EMAIL_VERIFICATION_EXPIRE=60

# Frontend URL for redirects after verification
APP_FRONTEND_URL=https://frontend.example.com

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Config Files

**config/auth.php:**
```php
'verification' => [
    'expire' => env('EMAIL_VERIFICATION_EXPIRE', 60),
],
```

**config/core.php:**
```php
'tenant_user_model' => env('TENANT_USER_MODEL', 'Ingenius\\Auth\\Models\\User'),
'central_user_model' => env('CENTRAL_USER_MODEL', 'Ingenius\\Core\\Models\\User'),
```

---

## Summary

✅ Email verification is **enabled by default** for both tenant and central users
✅ Uses **`?tenant=` query parameter** for reliable multi-tenancy support
✅ **Custom User models** are supported with proper configuration
✅ **Login is blocked** until email is verified
✅ Use `php artisan auth:setup-email-verification` to verify your setup

For questions or issues, check the [troubleshooting section](#troubleshooting) or review the code in:
- [TenantVerifyEmail.php](packages/ingenius/auth/src/Notifications/TenantVerifyEmail.php)
- [TenantAuthController.php](packages/ingenius/auth/src/Http/Controllers/TenantAuthController.php)
- [tenant.php routes](packages/ingenius/auth/routes/tenant.php)
