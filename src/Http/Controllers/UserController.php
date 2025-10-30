<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Ingenius\Auth\Actions\ListUsersAction;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Auth\Http\Requests\UserUpdateRequest;
use Ingenius\Auth\Http\Resources\UserResource;
use Ingenius\Auth\Models\User;
use Ingenius\Core\Http\Controllers\Controller;
use Ingenius\Core\Interfaces\HasCustomerProfile;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, ListUsersAction $listUsersAction): JsonResponse
    {
        $user = AuthHelper::getUser();

        $this->authorizeForUser($user, 'viewAny', User::class);

        $users = $listUsersAction($request->all());

        return Response::api(
            data: UserResource::collection($users),
            message: 'Users retrieved successfully'
        );
    }

    public function show(User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'view', $user);

        return Response::api(data: UserResource::make($user), message: 'User retrieved successfully');
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'update', $user);

        $user->update($request->validated());

        // Update profile if user implements HasCustomerProfile
        if ($user instanceof HasCustomerProfile) {
            $profileData = $request->only(['name', 'lastname', 'phone', 'address']);
            if (!empty(array_filter($profileData))) {
                $user->updateProfile([...$profileData, 'firstname' => $profileData['name']]);
            }
        }

        return Response::api(data: UserResource::make($user->fresh('profile')), message: 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'delete', $user);

        $user->delete();

        return Response::api(message: 'User deleted successfully');
    }
}
