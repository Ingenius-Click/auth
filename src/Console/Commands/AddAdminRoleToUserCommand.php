<?php

namespace Ingenius\Auth\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Auth\Models\Permission;
use Ingenius\Auth\Models\Role;
use Ingenius\Auth\Models\User;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Support\PermissionsManager;
use Stancl\Tenancy\Tenancy;

class AddAdminRoleToUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ingenius:auth:add-admin-role-to-user 
                            {tenant : The tenant ID} 
                            {user : The user ID or email in the tenant database}';

    /**
     * The console command description.
     */
    protected $description = 'Adds an admin role to an existing user in a tenant and assigns all permissions to that role';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected Tenancy $tenancy,
        protected PermissionsManager $permissionsManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $userIdentifier = $this->argument('user');

        // Find the tenant
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant with ID {$tenantId} not found.");
            return 1;
        }

        // Initialize tenant context
        $this->tenancy->initialize($tenant);

        try {
            // Find the user by ID or email
            $user = is_numeric($userIdentifier)
                ? User::find($userIdentifier)
                : User::where('email', $userIdentifier)->first();

            if (!$user) {
                $this->error("User with identifier {$userIdentifier} not found in tenant {$tenantId}.");
                $this->tenancy->end();
                return 1;
            }

            // Create or get the admin role
            $adminRole = Role::firstOrCreate(
                ['name' => 'admin', 'guard_name' => 'tenant'],
                ['description' => 'Administrator with all permissions']
            );

            // Get all permissions in the tenant context
            $permissions = Permission::all();

            // If no permissions found, maybe they need to be synced first
            if ($permissions->isEmpty()) {
                if ($this->confirm('No permissions found in tenant database. Would you like to sync permissions first?', true)) {
                    $this->call('ingenius:auth:sync-tenant-permissions', ['--tenant' => $tenantId]);
                    // Refresh permissions after sync
                    $permissions = Permission::all();
                }
            }

            // Assign all permissions to the admin role
            $adminRole->syncPermissions($permissions);
            $this->info('Assigned ' . $permissions->count() . ' permissions to admin role.');

            // Assign the admin role to the user
            $user->assignRole($adminRole);

            $this->info("Successfully assigned admin role to user {$user->name} (ID: {$user->id}) in tenant {$tenantId}.");

            $this->tenancy->end();
            return 0;
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            $this->tenancy->end();
            return 1;
        }
    }
}
