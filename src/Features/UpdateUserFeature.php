<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class UpdateUserFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'update-user';
    }

    public function getName(): string
    {
        return 'Update user';
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
