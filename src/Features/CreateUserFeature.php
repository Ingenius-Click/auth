<?php

namespace Ingenius\Auth\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class CreateUserFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'create-user';
    }

    public function getName(): string
    {
        return __('Create user');
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
