<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ListPermissionsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'list-permissions';
    }

    public function getName(): string
    {
        return __('List permissions');
    }

    public function getGroup(): string
    {
        return __('Permissions');
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
