<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\ISocialAuthenticationValidator;
use Illuminate\Support\Facades\Validator;

class SocialAuthenticationValidator implements ISocialAuthenticationValidator
{

    public function validateCodeAndProvider($data)
    {
        $rules = [
            "code" => "required|max:2000",
            "provider" => "required|max:50"
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateRedirectProvider($data)
    {
        $rules = [
            "provider" => "required|max:50"
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

}


