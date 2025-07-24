<?php

namespace Ingenius\Auth\Policies;

use Ingenius\Auth\Constants\AuthPermissions;
use Ingenius\Auth\Models\Role;
use Ingenius\Auth\Models\User;

class RolePolicy
{
    public function viewAny(?User $user)
    {
        return $user && $user->can(AuthPermissions::ROLES_VIEW);
    }

    public function view(?User $user, Role $targetRole)
    {
        return $user && $user->can(AuthPermissions::ROLES_VIEW);
    }

    public function create(User $user)
    {
        return $user->can(AuthPermissions::ROLES_CREATE);
    }

    public function update(User $user, Role $targetRole)
    {
        return $user->can(AuthPermissions::ROLES_EDIT);
    }

    public function delete(User $user, Role $targetRole)
    {
        return $user->can(AuthPermissions::ROLES_DELETE);
    }
}
