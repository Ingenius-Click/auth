<?php

namespace Ingenius\Auth\Constants;

class AuthPermissions
{
    // Users permissions
    public const USERS_VIEW = 'auth.users.view';
    public const USERS_CREATE = 'auth.users.create';
    public const USERS_EDIT = 'auth.users.edit';
    public const USERS_DELETE = 'auth.users.delete';

    // Roles permissions
    public const ROLES_VIEW = 'auth.roles.view';
    public const ROLES_CREATE = 'auth.roles.create';
    public const ROLES_EDIT = 'auth.roles.edit';
    public const ROLES_DELETE = 'auth.roles.delete';

    // Permissions management
    public const PERMISSIONS_VIEW = 'auth.permissions.view';
    public const PERMISSIONS_ASSIGN = 'auth.permissions.assign';
}
