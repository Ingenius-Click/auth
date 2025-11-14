<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ingenius\Auth\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        // Add new columns to permissions table
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->string('group')->nullable()->after('display_name');
        });

        // Populate display_name and group for existing permissions
        $this->populatePermissionData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropColumn(['display_name', 'group']);
        });
    }

    /**
     * Populate display_name and group fields for existing permissions
     */
    protected function populatePermissionData(): void
    {
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $data = $this->derivePermissionData($permission->name);

            $permission->update([
                'display_name' => $data['display_name'],
                'group' => $data['group'],
            ]);
        }
    }

    /**
     * Derive display_name and group from permission name
     *
     * Expected format: "resource.action" or "module:resource.action"
     * Examples:
     * - "products.view" -> group: "Products", display_name: "View Products"
     * - "products.create" -> group: "Products", display_name: "Create Products"
     * - "categories.view" -> group: "Categories", display_name: "View Categories"
     */
    protected function derivePermissionData(string $permissionName): array
    {
        // Default fallback
        $displayName = ucwords(str_replace(['.', '_', '-'], ' ', $permissionName));
        $group = 'General';

        // Parse permission name (format: "resource.action" or "module:resource.action")
        if (str_contains($permissionName, '.')) {
            $parts = explode('.', $permissionName);

            if (count($parts) >= 2) {
                $resource = $parts[0];
                $action = $parts[1];

                // Handle module prefix (e.g., "shop:products")
                if (str_contains($resource, ':')) {
                    $resourceParts = explode(':', $resource);
                    $resource = end($resourceParts);
                }

                // Generate group (capitalize and singularize/pluralize as needed)
                $group = ucfirst($resource);

                // Generate display name (Action + Resource)
                // e.g., "view" + "products" -> "View Products"
                $actionLabel = ucfirst($action);
                $resourceLabel = ucfirst($resource);
                $displayName = "{$actionLabel} {$resourceLabel}";
            }
        }

        return [
            'display_name' => $displayName,
            'group' => $group,
        ];
    }
};
