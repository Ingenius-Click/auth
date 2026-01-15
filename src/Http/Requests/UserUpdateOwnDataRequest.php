<?php

namespace Ingenius\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ingenius\Core\Helpers\AuthHelper;

class UserUpdateOwnDataRequest extends FormRequest
{
    protected $user;
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $logged = AuthHelper::getUser();

        $userClass = tenant_user_class();

        $this->user = $userClass::find($logged->id);

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
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            ... $extraRules,
        ];
    }

    public function loggedUser(){
        return $this->user;
    }
}