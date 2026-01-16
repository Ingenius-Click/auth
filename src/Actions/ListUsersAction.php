<?php

namespace Ingenius\Auth\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Ingenius\Auth\Models\User;

class ListUsersAction
{
    public function __invoke(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with('profile');

        return table_handler_paginate($filters, $query);
    }
}
