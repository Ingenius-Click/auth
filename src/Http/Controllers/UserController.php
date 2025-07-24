<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ingenius\Auth\Actions\ListUsersAction;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Auth\Http\Requests\UserUpdateRequest;
use Ingenius\Auth\Models\User;
use Ingenius\Core\Http\Controllers\Controller;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, ListUsersAction $listUsersAction): JsonResponse
    {
        $user = AuthHelper::getUser();

        $this->authorizeForUser($user, 'viewAny', User::class);

        $users = $listUsersAction($request->all());

        return response()->api(data: $users, message: 'Users retrieved successfully');
    }

    public function show(User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'view', $user);

        return response()->api(data: $user, message: 'User retrieved successfully');
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'update', $user);

        $user->update($request->validated());

        return response()->api(data: $user, message: 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'delete', $user);

        $user->delete();

        return response()->api(message: 'User deleted successfully');
    }
}
