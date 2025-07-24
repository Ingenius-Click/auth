<?php

namespace Ingenius\Auth\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Auth\Models\Permission;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Support\PermissionsManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Tenancy;

class SyncTenantPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ingenius:auth:sync-tenant-permissions {--tenant= : The tenant ID to sync permissions for. If not provided, sync for all tenants}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize permissions from PermissionsManager to tenant databases';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected PermissionsManager $permissionsManager,
        protected Tenancy $tenancy
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);

            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }

            $this->syncPermissionsForTenant($tenant);
            $this->info("Permissions synchronized for tenant {$tenantId}.");
            return 0;
        }

        // Sync for all tenants
        $tenants = Tenant::all();
        $tenantCount = count($tenants);

        if ($tenantCount === 0) {
            $this->info("No tenants found to synchronize permissions.");
            return 0;
        }

        $this->info("Synchronizing permissions for {$tenantCount} tenants...");

        $progressBar = $this->output->createProgressBar($tenantCount);
        $progressBar->start();

        foreach ($tenants as $tenant) {
            // Cast to TenantWithDatabase since we know our Tenant model implements this interface
            if ($tenant instanceof TenantWithDatabase) {
                $this->syncPermissionsForTenant($tenant);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Permissions synchronized for all tenants.");

        return 0;
    }

    /**
     * Sync permissions for a specific tenant.
     */
    protected function syncPermissionsForTenant(TenantWithDatabase $tenant): void
    {
        $this->tenancy->initialize($tenant);

        $permissions = $this->permissionsManager->tenant();

        foreach ($permissions as $permissionName => $permissionData) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'tenant',
            ], [
                'description' => $permissionData['description'],
            ]);
        }

        $this->tenancy->end();
    }
}
