<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IPropertyValidator;
use App\Components\Validators\IFormValidator;
use Illuminate\Support\Facades\Validator;

class PropertyValidator implements IPropertyValidator, IFormValidator
{

    public function validatePropertyReference($reference)
    {
        $rules = [
            "property_ref" => "required",
        ];

        $messages = [];

        return Validator::make($reference, $rules, $messages);
    }

    public function validate($request)
    {
        return $this->propertyEnquiryValidator($request);
    }

    private function propertyEnquiryValidator($data)
    {
        $rules = [
            'property_id'   => 'required', 
            'consultant_id' => 'required', 
            'fullname'      => 'required',
            'email'         => 'required|email', 
            'phone_number'  => 'nullable',
            'comment'       => 'required', 
            // 'g-recaptcha-response' => 'required|recaptcha',
        ];

        $messages = [
            'g-recaptcha-response.recaptcha' => 'Captcha verification failed',
            'g-recaptcha-response.required'  => 'Please complete the captcha'
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function validateNewSubmitted($data)
    {
        $rules = [
            'recipient' => 'required|emails',
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
            'prefix_phone_number' => 'required',
            'phone_number' => 'required',
            'location' => 'required',
            'residential' => 'nullable',
            'number_of_bedroom' => 'nullable',
            'number_of_bathroom' => 'nullable',
            'purpose' => 'nullable',
            'property_description' => 'nullable',
            'agreement' => 'accepted',
            // 'g-recaptcha-response' => 'required|recaptcha',
        ];

        $messages = [
            'g-recaptcha-response.recaptcha' => 'Captcha verification failed',
            'g-recaptcha-response.required' => 'Please complete the captcha'
        ];

        return Validator::make($data, $rules, $messages);
    }
}
