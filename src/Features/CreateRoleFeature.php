<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class CreateRoleFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'create-role';
    }

    public function getName(): string
    {
        return 'Create role';
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
