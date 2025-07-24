<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Auth\Models\Permission;
use Ingenius\Core\Support\PermissionsManager;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected PermissionsManager $permissionsManager
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', Permission::class);

        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions
        ]);
    }

    /**
     * Display the registry of all available permissions.
     */
    public function registry()
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', Permission::class);

        return response()->json([
            'permissions' => $this->permissionsManager->all()
        ]);
    }

    /**
     * Sync the permissions from the registry to the database.
     */
    public function sync()
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'assign', Permission::class);

        $registeredPermissions = $this->permissionsManager->all();
        $syncedCount = 0;

        foreach ($registeredPermissions as $permissionName => $permissionData) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'tenant',
            ], [
                'description' => $permissionData['description'],
            ]);

            $syncedCount++;
        }

        return response()->json([
            'message' => $syncedCount . ' permissions synchronized successfully'
        ]);
    }

    /**
     * Get permissions by module.
     */
    public function byModule(string $module)
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', Permission::class);

        $modulePermissions = $this->permissionsManager->forModule($module);

        return response()->json([
            'module' => $module,
            'permissions' => $modulePermissions
        ]);
    }
}
