<?php

namespace Ingenius\Auth\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'display_name',
        'group',
    ];

    /**
     * Derive display_name and group from permission name
     *
     * Expected format: "resource.action" or "module:resource.action"
     * Examples:
     * - "products.view" -> group: "Products", display_name: "View Products"
     * - "products.create" -> group: "Products", display_name: "Create Products"
     */
    public static function derivePermissionData(string $permissionName): array
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
}
