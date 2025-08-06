<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ListRolesFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'list-roles';
    }

    public function getName(): string
    {
        return __('List roles');
    }

    public function getGroup(): string
    {
        return __('Roles');
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
