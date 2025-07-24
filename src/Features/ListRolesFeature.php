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
        return 'List roles';
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
