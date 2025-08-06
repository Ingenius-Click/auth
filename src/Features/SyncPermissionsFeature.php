<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class SyncPermissionsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'sync-permissions';
    }

    public function getName(): string
    {
        return __('Sync permissions');
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
