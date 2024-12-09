<?php

namespace App\Components\Validators;

interface IAccountValidator
{
    public function validateFirstUpdateAccount($data);

    public function validateUpdateAccount($data);

    public function validateDeactivationAccount($data);

    public function validateAccountEmailChange($data);

    public function validateNotificationChange($data);
}