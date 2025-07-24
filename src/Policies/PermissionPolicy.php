<?php

namespace Ingenius\Auth\Policies;

use Ingenius\Auth\Constants\AuthPermissions;
use Ingenius\Auth\Models\Permission;
use Ingenius\Auth\Models\User;

class PermissionPolicy
{
    public function viewAny(?User $user)
    {
        return $user && $user->can(AuthPermissions::PERMISSIONS_VIEW);
    }

    public function view(?User $user, Permission $targetPermission)
    {
        return $user && $user->can(AuthPermissions::PERMISSIONS_VIEW);
    }

    public function assign(User $user)
    {
        return $user->can(AuthPermissions::PERMISSIONS_ASSIGN);
    }
}
