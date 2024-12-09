<?php

namespace App\Components\Validators;

interface IPropertyValidator
{
    public function validatePropertyReference($reference);

    public function validateNewSubmitted($data);
}