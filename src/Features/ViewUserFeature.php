<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ViewUserFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'view-user';
    }

    public function getName(): string
    {
        return __('View user');
    }

    public function getGroup(): string
    {
        return __('Users');
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
