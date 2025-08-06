<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ListUsersFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'list-users';
    }

    public function getName(): string
    {
        return __('List users');
    }

    public function getGroup(): string
    {
        return __('Users');
    }

    public function getPackage(): string
    {
        return 'auth';
    }

    public function isBasic(): bool
    {
        return true;
    }
}
