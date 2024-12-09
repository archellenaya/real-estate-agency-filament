<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IFormValidator;
use Illuminate\Support\Facades\Validator;

class AgentValidator implements IFormValidator
{
    public function validate($data)
    {
        $rules = [
            'recipient'       => 'required|emails',
            'fullname'        => 'required',
            'email'           => 'required|email',
            'country'         => 'required',
            'review_comment'  => 'required',
            // 'g-recaptcha-response' => 'required|recaptcha',
        ];

        $messages = [
            'g-recaptcha-response.recaptcha' => 'Captcha verification failed',
            'g-recaptcha-response.required' => 'Please complete the captcha'
        ];

        return Validator::make($data, $rules, $messages);
    }
}
