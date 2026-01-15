<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Ingenius\Auth\Actions\ListUsersAction;
use Ingenius\Auth\Http\Requests\UserChangePasswordRequest;
use Ingenius\Auth\Http\Requests\UserStoreRequest;
use Ingenius\Auth\Http\Requests\UserUpdateOwnDataRequest;
use Ingenius\Auth\Http\Requests\UserUpdateRequest;
use Ingenius\Auth\Http\Resources\UserResource;
use Ingenius\Auth\Models\User;
use Ingenius\Core\Helpers\AuthHelper;
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

    public function store(UserStoreRequest $request): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'create', User::class);

        $userClass = tenant_user_class();

        // Create the user
        $user = $userClass::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'email_verified_at' => now(),
        ]);

        // Create profile if user implements HasCustomerProfile
        if ($user instanceof HasCustomerProfile) {
            $profileData = $request->only(['lastname', 'phone', 'address']);
            if (!empty(array_filter($profileData))) {
                $user->updateProfile([...$profileData, 'firstname' => $profileData['name']]);
            }
        }

        // Assign roles if provided
        if ($request->has('roles')) {
            $roles = $request->input('roles');
            if ($roles && is_array($roles) && count($roles) > 0) {
                $user->syncRoles($roles);
            }
        }

        return Response::api(data: UserResource::make($user->fresh('profile')), message: 'User created successfully', code: 201);
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

        // Handle roles assignment
        if ($request->has('roles')) {
            $newRoles = $request->input('roles');

            if ($newRoles && is_array($newRoles) && count($newRoles) > 0) {
                // Remove all existing roles and assign the new ones
                $user->syncRoles($newRoles);
            } else {
                // If roles is null or empty array, remove all roles
                $user->syncRoles([]);
            }
        }

        return Response::api(data: UserResource::make($user->fresh('profile')), message: 'User updated successfully');
    }

    public function updateOwnProfile(UserUpdateOwnDataRequest $request): JsonResponse {

        $user = $request->loggedUser();

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

    public function changePassword(UserChangePasswordRequest $request): JsonResponse
    {
        $user = $request->loggedUser();

        $user->update([
            'password' => bcrypt($request->input('new_password'))
        ]);

        return Response::api(data: null, message: 'Password changed successfully');
    }

    public function deleteOwnAccount(): JsonResponse
    {
        $user = AuthHelper::getUser();

        $userClass = tenant_user_class();
        $userModel = $userClass::find($user->getAuthIdentifier());

        if (!$userModel) {
            return Response::api(data: null, message: 'User not found', code: 404);
        }

        // This will trigger soft delete and anonymization via the AnonymizesUserData trait
        $userModel->delete();

        return Response::api(data: null, message: 'Your account has been deleted successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $currentUser = AuthHelper::getUser();

        $this->authorizeForUser($currentUser, 'delete', $user);

        $user->delete();

        return Response::api(message: 'User deleted successfully');
    }
}
