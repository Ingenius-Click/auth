<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Routing\Controller;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Auth\Models\Role;
use Ingenius\Core\Support\PermissionsManager;

class RoleController extends Controller
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
        $this->authorizeForUser($user, 'viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return ResponseFacade::api(data: $roles, message: 'Roles retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'create', Role::class);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'tenant',
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return ResponseFacade::api(data: $role, message: 'Role retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'update', $role);

        if (isset($validated['name'])) {
            $role->name = $validated['name'];
        }

        if (array_key_exists('description', $validated)) {
            $role->description = $validated['description'];
        }

        $role->save();

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->fresh(['permissions'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'delete', $role);

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all available permissions.
     */
    public function getPermissions()
    {
        return response()->json([
            'permissions' => $this->permissionsManager->all()
        ]);
    }
}
