<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the authentication settings for the auth package.
    |
    */

    // The default guard for tenant authentication
    'tenant_guard' => 'tenant',

    // The default guard for API authentication
    'api_guard' => 'sanctum',

    // The model to use for tenant users
    'tenant_user_model' => \Ingenius\Auth\Models\User::class,

    // Password reset settings
    'password_reset' => [
        'token_expiration' => 60, // minutes
        'throttle' => 60, // seconds
    ],

    // Session settings
    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
    ],

    // Default roles
    'roles' => [
        'admin' => [
            'name' => 'admin',
            'description' => 'Administrator with all permissions',
        ],
        'user' => [
            'name' => 'user',
            'description' => 'Regular user with limited permissions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Classes
    |--------------------------------------------------------------------------
    |
    | Here you can register settings classes for the auth package.
    |
    */
    'settings_classes' => [
        \Ingenius\Auth\Settings\AuthSettings::class,
    ],
];
