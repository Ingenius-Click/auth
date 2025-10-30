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

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }
}
