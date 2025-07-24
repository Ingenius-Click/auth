<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Auth\Http\Controllers\PermissionController;
use Ingenius\Auth\Http\Controllers\RoleController;
use Ingenius\Auth\Http\Controllers\TenantAuthController;
use Ingenius\Auth\Http\Controllers\UserController;

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
