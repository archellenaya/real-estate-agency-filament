<?php 

namespace App\Components\Validators;

interface IPropertyAlertValidator
{
    public function validatePropertyAlertId($data);

    public function validateStorePropertyAlert($data);

    public function validateUpdatePropertyAlert($data);
}