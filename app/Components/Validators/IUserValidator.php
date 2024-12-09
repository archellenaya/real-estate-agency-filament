<?php

namespace App\Components\Validators;

interface IUserValidator
{
    public function validateCreateUser($data);

    public function validateUserId($data);

    public function validateChangePassword($data);
}