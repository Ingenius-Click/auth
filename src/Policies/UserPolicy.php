<?php

namespace Ingenius\Auth\Policies;

use Ingenius\Auth\Constants\AuthPermissions;
use Ingenius\Auth\Models\User;

class UserPolicy
{
    public function viewAny(?User $user)
    {
        return $user && $user->can(AuthPermissions::USERS_VIEW);
    }

    public function view(?User $user, User $targetUser)
    {
        return $user && $user->can(AuthPermissions::USERS_VIEW);
    }

    public function create(User $user)
    {
        return $user->can(AuthPermissions::USERS_CREATE);
    }

    public function update(User $user, User $targetUser)
    {
        return $user->can(AuthPermissions::USERS_EDIT);
    }

    public function delete(User $user, User $targetUser)
    {
        return $user->can(AuthPermissions::USERS_DELETE);
    }
}
