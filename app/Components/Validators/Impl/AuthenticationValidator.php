<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IAuthenticationValidator;
use Illuminate\Support\Facades\Validator;

class AuthenticationValidator implements IAuthenticationValidator
{

    public function validateLogin($data)
    {
        $rules = [
            "username" => "required|string|max:255",
            "password" => "required|min:8",
        ];

        $messages = [
            'username.required' => 'The username is required.',
            'username.string' => 'The username must be a string.',
            'username.max' => 'The username may not be greater than 255 characters.',

            'password.required' => 'The password is required.',
            'password.min' => 'The password must be at least 8 characters long.',
        ];

        return Validator::make($data, $rules, $messages);
    }
    public function validateRegister($data)
    {
        $rules = [
            'firstname' => "required|string|max:255",
            'lastname' => "required|string|max:255",
            "username" => "required|string|max:255|unique:users",
            "email" => "required|email:rfc,filter|max:255|unique:users",
            'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
        ];

        $messages = [
            'firstname.required' => 'The first name is required.',
            'firstname.string' => 'The first name must be a string.',
            'firstname.max' => 'The first name may not be greater than 255 characters.',

            'lastname.required' => 'The last name is required.',
            'lastname.string' => 'The last name must be a string.',
            'lastname.max' => 'The last name may not be greater than 255 characters.',

            'username.required' => 'The username is required.',
            'username.string' => 'The username must be a string.',
            'username.max' => 'The username may not be greater than 255 characters.',
            'username.unique' => 'The username has already been taken.',

            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'The email has already been taken.',

            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must contain at least 1 uppercase letter, 1 lowercase letter, 1 numeric digit, and 1 special character.',
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function validateEmail($data)
    {
        $rules = [
            "email" => "required|email:rfc,filter|max:255|exists:users",
        ];

        $messages = [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.exists' => 'The provided email does not exist in our records.',
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function validateCode($data)
    {
        $rules = [
            "code" => "required|max:50",
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateCodeAndPassword($data)
    {
        $rules = [
            "code" => "required|max:50",
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
            'password_confirmation' => 'required|min:8',
        ];

        $messages = [
            'code.required' => 'The code is required.',
            'code.max' => 'The code may not be greater than 50 characters.',

            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password must be at least 8 characters long, and should contain at least 1 uppercase letter, 1 lowercase letter, 1 numeric digit, and 1 special character.',

            'password_confirmation.required' => 'The password confirmation is required.',
            'password_confirmation.min' => 'The password confirmation must be at least 8 characters long.',
        ];

        return Validator::make($data, $rules, $messages);
    }
}
