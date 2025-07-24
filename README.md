# Ingenius Auth Package

This package provides authentication functionality for the Ingenius platform, including:

- User authentication for tenants
- Role and permission management
- Middleware for tenant authentication and permission checks
- Helper functions for authentication

## Installation

```bash
composer require ingenius/auth
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ingenius-auth-config
```

## Migrations

Publish the migrations:

```bash
php artisan vendor:publish --tag=ingenius-auth-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Usage

### Authentication

```php
use Ingenius\Auth\Helpers\AuthHelper;

// Get authenticated user from any guard
$user = AuthHelper::getUser();

// Check if user is authenticated in any guard
if (AuthHelper::check()) {
   // User is authenticated
}

// Get user from a specific guard
$tenantUser = AuthHelper::getUserFromGuard('tenant');
$sanctumUser = AuthHelper::getUserFromGuard('sanctum');
```

### Middleware

The package provides two middleware:

- `tenant.user`: Ensures the user is authenticated in the tenant context
- `tenant.permission`: Checks if the user has the required permission

```php
// In your routes file
Route::middleware(['tenant.user'])->group(function () {
    Route::get('/dashboard', 'DashboardController@index');
});

Route::middleware(['tenant.user', 'tenant.permission:auth.users.view'])->group(function () {
    Route::get('/users', 'UserController@index');
});
```

### Commands

The package provides the following commands:

- `ingenius:auth:sync-tenant-permissions`: Synchronizes permissions from the PermissionsManager to tenant databases
- `ingenius:auth:add-admin-role-to-user`: Adds an admin role to an existing user in a tenant and assigns all permissions to that role

```bash
# Sync permissions for all tenants
php artisan ingenius:auth:sync-tenant-permissions

# Sync permissions for a specific tenant
php artisan ingenius:auth:sync-tenant-permissions --tenant=1

# Add admin role to a user
php artisan ingenius:auth:add-admin-role-to-user 1 user@example.com
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.