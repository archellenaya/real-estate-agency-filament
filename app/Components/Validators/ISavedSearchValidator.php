<?php 

namespace App\Components\Validators;

interface ISavedSearchValidator
{
    public function validateSavedSearchId($data);
    
    public function validateStoreSavedSearch($data);

    public function validateUpdateSavedSearch($data);
}