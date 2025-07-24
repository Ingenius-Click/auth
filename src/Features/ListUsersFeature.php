<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ListUsersFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'list-users';
    }

    public function getName(): string
    {
        return 'List users';
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
