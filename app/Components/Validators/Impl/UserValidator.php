<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IUserValidator;
use Illuminate\Support\Facades\Validator;

class UserValidator implements IUserValidator
{
    public function validateCreateUser($data)
    {
        $rules = [
            "username" => "required|email:rfc,filter|unique:users|max:100",
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateUserId($data)
    {
        $rules = [
            "user_id" => "required|integer",
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateChangePassword($data)
    {
        $rules = [
            "current_password" => "required|string|min:8",
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
            'password_confirmation' => 'required|min:8',
        ];

        $messages = [
            'current_password.required' => 'The current password is required.',
            'current_password.string' => 'The current password must be a valid string.',
            'current_password.min' => 'The current password must be at least 8 characters long.',

            'password.required' => 'The new password is required.',
            'password.string' => 'The new password must be a valid string.',
            'password.min' => 'The new password must be at least 8 characters long.',
            'password.confirmed' => 'The new password confirmation does not match.',
            'password.regex' => 'The new password must be at least 8 characters long, and should contain at least 1 uppercase, 1 lowercase, 1 numeric, and 1 special character.',

            'password_confirmation.required' => 'The password confirmation is required.',
            'password_confirmation.min' => 'The password confirmation must be at least 8 characters long.',
        ];

        return Validator::make($data, $rules, $messages);
    }
}
