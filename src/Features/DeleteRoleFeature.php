<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class DeleteRoleFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'delete-role';
    }

    public function getName(): string
    {
        return 'Delete role';
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
