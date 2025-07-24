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
        return 'List permissions';
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
