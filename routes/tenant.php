<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Ingenius\Auth\Http\Controllers\PermissionController;
use Ingenius\Auth\Http\Controllers\RoleController;
use Ingenius\Auth\Http\Controllers\TenantAuthController;
use Ingenius\Auth\Http\Controllers\UserController;
use Ingenius\Auth\Http\Requests\TenantEmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the tenant middleware. Now create something great!
|
*/

Route::prefix('auth')->name('auth.')->middleware('web')->group(function () {
    // Add your tenant routes here
});

// API Authentication Routes
Route::prefix('api')->middleware(['api'])->group(function () {
    Route::post('/login', [TenantAuthController::class, 'login']);
    Route::post('/register', [TenantAuthController::class, 'register']);

    // Email Verification Routes
    Route::prefix('email')->group(function () {
        // Verification notice (for users who need to verify)
        Route::get('/verify', function () {
            if (request()->wantsJson()) {
                return Response::api(
                    data: null,
                    message: 'Your email address is not verified. Please check your email for a verification link.',
                    code: 403
                );
            }
            // Redirect to frontend or return view
            return redirect(config('app.frontend_url', '/') . '/email/verify');
        })->middleware(['tenant.user'])->name('verification.notice');

        // Email verification handler
        // NOTE: Tenancy is initialized by domain/query param, so user doesn't need to be authenticated
        Route::get('/verify/{id}/{hash}', function (TenantEmailVerificationRequest $request) {
            $request->fulfill();

            if ($request->wantsJson()) {
                return Response::api(
                    data: ['verified' => true],
                    message: 'Email verified successfully!'
                );
            }

            // Get redirect URL from tenant settings
            $authSettings = \Ingenius\Auth\Settings\AuthSettings::make();
            $redirectUrl = $authSettings->email_verification_redirect_url;

            // If no custom URL is set, use default frontend URL
            if (empty($redirectUrl)) {
                $redirectUrl = config('app.frontend_url', '/') . '/email/verified';
            }

            return redirect($redirectUrl);
        })->middleware(['signed:tenant'])->name('verification.verify'); // Ignore 'tenant' param in signature validation

        // Resend verification email
        Route::post('/verification-notification', function (Request $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return Response::api(
                    data: null,
                    message: 'Email already verified.',
                    code: 400
                );
            }

            $request->user()->sendEmailVerificationNotification();

            if ($request->wantsJson()) {
                return Response::api(
                    data: null,
                    message: 'Verification link sent!'
                );
            }

            return back()->with('message', 'Verification link sent!');
        })->middleware(['tenant.user', 'throttle:6,1'])->name('verification.send');
    });

    // Protected API Routes
    Route::middleware(['tenant.user'])->group(function () {
        Route::post('/logout', [TenantAuthController::class, 'logout']);
        Route::get('/user', [TenantAuthController::class, 'user']);

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('tenant.has.feature:list-users');
            Route::get('/{user}', [UserController::class, 'show'])->middleware('tenant.has.feature:view-user');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('tenant.has.feature:update-user');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('tenant.has.feature:delete-user');
        });

        // Role and Permission Management Routes
        // These routes are protected by the 'permission' middleware
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->middleware('tenant.has.feature:list-roles');
            Route::get('/permissions', [RoleController::class, 'getPermissions'])->middleware('tenant.has.feature:list-permissions');
            Route::get('/{id}', [RoleController::class, 'show'])->middleware('tenant.has.feature:view-role');

            Route::post('/', [RoleController::class, 'store'])->middleware('tenant.has.feature:create-role');

            Route::put('/{id}', [RoleController::class, 'update'])->middleware('tenant.has.feature:update-role');

            Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware('tenant.has.feature:delete-role');
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->middleware('tenant.has.feature:list-permissions');
            Route::get('/registry', [PermissionController::class, 'registry'])->middleware('tenant.has.feature:list-permissions');
            Route::get('/module/{module}', [PermissionController::class, 'byModule'])->middleware('tenant.has.feature:list-permissions');

            Route::post('/sync', [PermissionController::class, 'sync'])->middleware('tenant.has.feature:sync-permissions');
        });
    });
});
