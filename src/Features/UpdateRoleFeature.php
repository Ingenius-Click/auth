<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class UpdateRoleFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'update-role';
    }

    public function getName(): string
    {
        return 'Update role';
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
