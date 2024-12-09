<?php

namespace App\Components\Validators;

interface ISocialAuthenticationValidator
{
    public function validateCodeAndProvider($data);

    public function validateRedirectProvider($data);
}