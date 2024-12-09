<?php

namespace App\Components\Validators;

interface IAuthenticationValidator
{
    public function validateLogin($data);

    public function validateRegister($data);

    public function validateEmail($data);

    public function validateCode($data);

    public function validateCodeAndPassword($data);
}