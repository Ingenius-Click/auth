<?php

namespace Ingenius\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userClass = tenant_user_class();

        $extraRules = [];

        if((new \ReflectionClass($userClass))->implementsInterface(\Ingenius\Core\Interfaces\HasCustomerProfile::class)) {
            $extraRules = [
                'lastname' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:500'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['required', 'numeric', 'exists:roles,id'],
            ... $extraRules,
        ];
    }
}
