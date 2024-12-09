<?php

namespace App\Components\Validators;

interface ISavedPropertyValidator
{
    public function validateSendEmailMoreInfo($data);

    public function validateShareSavedProperty($data);
}