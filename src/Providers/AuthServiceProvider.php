<?php

namespace Ingenius\Auth\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Ingenius\Auth\Console\Commands\AddAdminRoleToUserCommand;
use Ingenius\Auth\Console\Commands\SyncTenantPermissionsCommand;
use Ingenius\Auth\Constants\AuthPermissions;
use Ingenius\Auth\Features\CreateRoleFeature;
use Ingenius\Auth\Features\DeleteRoleFeature;
use Ingenius\Auth\Features\DeleteUserFeature;
use Ingenius\Auth\Features\ListPermissionsFeature;
use Ingenius\Auth\Features\ListRolesFeature;
use Ingenius\Auth\Features\ListUsersFeature;
use Ingenius\Auth\Features\SyncPermissionsFeature;
use Ingenius\Auth\Features\UpdateRoleFeature;
use Ingenius\Auth\Features\UpdateUserFeature;
use Ingenius\Auth\Features\ViewRoleFeature;
use Ingenius\Auth\Features\ViewUserFeature;
use Ingenius\Auth\Initializers\AuthTenantInitializer;
use Ingenius\Auth\Models\Permission;
use Ingenius\Auth\Models\Role;
use Ingenius\Auth\Models\User;
use Ingenius\Auth\Policies\PermissionPolicy;
use Ingenius\Auth\Policies\RolePolicy;
use Ingenius\Auth\Policies\UserPolicy;
use Ingenius\Core\Services\FeatureManager;
use Ingenius\Core\Support\PermissionsManager;
use Ingenius\Core\Support\TenantInitializationManager;
use Ingenius\Core\Traits\RegistersConfigurations;
use Ingenius\Core\Traits\RegistersMigrations;

class AuthServiceProvider extends ServiceProvider
{
    use RegistersMigrations, RegistersConfigurations;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/auth.php',
            'ingenius.auth'
        );

        // Register configuration with the registry
        $this->registerConfig(
            __DIR__ . '/../../config/auth.php',
            'ingenius.auth',
            'auth'
        );

        // Register the route service provider
        $this->app->register(RouteServiceProvider::class);

        $this->app->afterResolving(FeatureManager::class, function (FeatureManager $manager) {
            $manager->register(new ListUsersFeature());
            $manager->register(new ViewUserFeature());
            $manager->register(new UpdateUserFeature());
            $manager->register(new DeleteUserFeature());
            $manager->register(new ListRolesFeature());
            $manager->register(new CreateRoleFeature());
            $manager->register(new ViewRoleFeature());
            $manager->register(new UpdateRoleFeature());
            $manager->register(new DeleteRoleFeature());
            $manager->register(new ListPermissionsFeature());
            $manager->register(new SyncPermissionsFeature());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register migrations with the registry
        $this->registerMigrations(__DIR__ . '/../../database/migrations', 'auth');

        // Check if there's a tenant migrations directory and register it
        $tenantMigrationsPath = __DIR__ . '/../../database/migrations/tenant';
        if (is_dir($tenantMigrationsPath)) {
            $this->registerTenantMigrations($tenantMigrationsPath, 'auth');
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTenantPermissionsCommand::class,
                AddAdminRoleToUserCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../config/auth.php' => config_path('ingenius/auth.php'),
            ], 'ingenius-auth-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations/ingenius/auth'),
            ], 'ingenius-auth-migrations');
        }

        // Register middlewares
        $this->registerMiddlewares();

        // Register permissions
        $this->registerPermissions();

        // Register policies
        $this->registerPolicies();

        // Register tenant initializer
        $this->registerTenantInitializer();
    }

    /**
     * Register middlewares
     */
    protected function registerMiddlewares(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('tenant.user', \Ingenius\Auth\Http\Middleware\EnsureTenantUser::class);
        $router->aliasMiddleware('tenant.permission', \Ingenius\Auth\Http\Middleware\CheckPermission::class);
    }

    /**
     * Register permissions
     */
    protected function registerPermissions(): void
    {
        $this->app->afterResolving(PermissionsManager::class, function (PermissionsManager $manager) {
            // Register user permissions
            $manager->register(
                AuthPermissions::USERS_VIEW,
                'View users',
                'Auth',
                'tenant',
                'View users',
                'Users'
            );

            $manager->register(
                AuthPermissions::USERS_CREATE,
                'Create users',
                'Auth',
                'tenant',
                'Create users',
                'Users'
            );

            $manager->register(
                AuthPermissions::USERS_EDIT,
                'Edit users',
                'Auth',
                'tenant',
                'Edit users',
                'Users'
            );

            $manager->register(
                AuthPermissions::USERS_DELETE,
                'Delete users',
                'Auth',
                'tenant',
                'Delete users',
                'Users'
            );

            // Register role permissions
            $manager->register(
                AuthPermissions::ROLES_VIEW,
                'View roles',
                'Auth',
                'tenant',
                'View roles',
                'Roles'
            );

            $manager->register(
                AuthPermissions::ROLES_CREATE,
                'Create roles',
                'Auth',
                'tenant',
                'Create roles',
                'Roles'
            );

            $manager->register(
                AuthPermissions::ROLES_EDIT,
                'Edit roles',
                'Auth',
                'tenant',
                'Edit roles',
                'Roles'
            );

            $manager->register(
                AuthPermissions::ROLES_DELETE,
                'Delete roles',
                'Auth',
                'tenant',
                'Delete roles',
                'Roles'
            );

            // Register permission management permissions
            $manager->register(
                AuthPermissions::PERMISSIONS_VIEW,
                'View permissions',
                'Auth',
                'tenant',
                'View permissions',
                'Permissions'
            );

            $manager->register(
                AuthPermissions::PERMISSIONS_ASSIGN,
                'Assign permissions',
                'Auth',
                'tenant',
                'Assign permissions',
                'Permissions'
            );
        });
    }

    /**
     * Register policies
     */
    protected function registerPolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }

    /**
     * Register tenant initializer
     */
    protected function registerTenantInitializer(): void
    {
        $this->app->afterResolving(TenantInitializationManager::class, function (TenantInitializationManager $manager) {
            $initializer = $this->app->make(AuthTenantInitializer::class);
            $manager->register($initializer);
        });
    }
}
