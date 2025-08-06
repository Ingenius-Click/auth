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
        return __('Update role');
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
