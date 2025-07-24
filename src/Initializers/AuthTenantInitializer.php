<?php

namespace Ingenius\Auth\Initializers;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ingenius\Auth\Models\Permission;
use Ingenius\Auth\Models\Role;
use Ingenius\Auth\Models\User;
use Ingenius\Core\Interfaces\TenantInitializer;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Support\PermissionsManager;

class AuthTenantInitializer implements TenantInitializer
{
    /**
     * Create a new initializer instance.
     */
    public function __construct(
        protected PermissionsManager $permissionsManager
    ) {}

    /**
     * Initialize a new tenant with required auth data
     *
     * @param Tenant $tenant
     * @param Command $command
     * @return void
     */
    public function initialize(Tenant $tenant, Command $command): void
    {
        // Sync permissions from the manager to the tenant database
        $this->syncPermissions();

        // Create admin role with all permissions
        $adminRole = $this->createAdminRole();

        // Create admin user and assign admin role
        $this->createAdminUser($adminRole, $command);
    }

    public function initializeViaRequest(Tenant $tenant, Request $request): void
    {
        $this->syncPermissions();

        $adminRole = $this->createAdminRole();

        $user = User::firstOrCreate([
            'email' => $request->user_email,
        ], [
            'name' => $request->user_name,
            'password' => Hash::make($request->user_password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($adminRole);
    }

    public function rules(): array
    {
        return [
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            'user_password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the priority of this initializer
     * Higher priority initializers run first
     *
     * @return int
     */
    public function getPriority(): int
    {
        // Auth should run first as other initializers may need users/permissions
        return 100;
    }

    /**
     * Get the name of this initializer
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Authentication Setup';
    }

    /**
     * Get the package name of this initializer
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return 'auth';
    }

    /**
     * Sync permissions from the manager to the tenant database
     *
     * @return void
     */
    protected function syncPermissions(): void
    {
        $permissions = $this->permissionsManager->tenant();

        foreach ($permissions as $permissionName => $permissionData) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'tenant',
            ], [
                'description' => $permissionData['description'],
            ]);
        }
    }

    /**
     * Create admin role with all permissions
     *
     * @return Role
     */
    protected function createAdminRole(): Role
    {
        // Create or get the admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'tenant'],
            ['description' => 'Administrator with all permissions']
        );

        // Get all permissions and assign to admin role
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        return $adminRole;
    }

    /**
     * Create admin user and assign admin role
     *
     * @param Role $adminRole
     * @param Command $command
     * @return User
     */
    protected function createAdminUser(Role $adminRole, Command $command): User
    {
        // Prompt for admin user details
        $command->info('Setting up admin user...');
        $name = $command->ask('Admin name', 'Admin');
        $email = $command->ask('Admin email', 'admin@example.com');
        $password = $command->secret('Admin password (leave empty for "password")') ?: 'password';

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $adminUser->assignRole($adminRole);

        $command->info("Admin user '{$name}' created with email '{$email}'");

        return $adminUser;
    }
}
