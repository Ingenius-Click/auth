<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class DeleteUserFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'delete-user';
    }

    public function getName(): string
    {
        return 'Delete user';
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
