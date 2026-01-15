<?php

namespace Ingenius\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Ingenius\Auth\Rules\PasswordRegex;
use Ingenius\Core\Helpers\AuthHelper;

class UserChangePasswordRequest extends FormRequest
{
    protected $user;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', new PasswordRegex()],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $loggedUser = AuthHelper::getUser();

            $userClass = tenant_user_class();
            $this->user = $userClass::find($loggedUser->getAuthIdentifier());

            if (!Hash::check($this->input('current_password'), $this->user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });
    }

    public function loggedUser()
    {
        return $this->user;
    }
}
