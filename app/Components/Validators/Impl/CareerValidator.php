<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IFormValidator;
use Illuminate\Support\Facades\Validator;

class CareerValidator implements IFormValidator
{

    public function validate($data) 
    {
        $rules = [
            'recipient' => 'required|emails',
            'fullname' => 'required',
            'prefix_phone_number' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email',
            'position' => 'required',  
            'cv_upload' => 'required',//|max:10000|mimes:doc,docx,pdf',
            'cover_letter_upload' => 'required',//|max:10000|mimes:doc,docx,pdf',
            'agreement' => 'required|accepted',
            // 'g-recaptcha-response' => 'required|recaptcha',
        ];

        $messages = [
            'g-recaptcha-response.recaptcha' => 'Captcha verification failed',
            'g-recaptcha-response.required' => 'Please complete the captcha'
        ];

        return Validator::make($data, $rules, $messages);
    }

}


