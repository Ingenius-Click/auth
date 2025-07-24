<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ViewRoleFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'view-role';
    }

    public function getName(): string
    {
        return 'View role';
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
