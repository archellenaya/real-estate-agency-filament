<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IPropertyAlertValidator;
use Illuminate\Support\Facades\Validator;

class PropertyAlertValidator implements IPropertyAlertValidator
{
    public function validatePropertyAlertId($data) 
    {
        $rules = [
            'id' => 'required|integer'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateStorePropertyAlert($data) 
    {
        $rules = [
            'name' => 'required|max:100',
            'type' => 'nullable',
            'property_type_id' => 'nullable|array',
            'location_id' => 'nullable|array',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateUpdatePropertyAlert($data) 
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required|max:100',
            'type' => 'nullable',
            'property_type_id' => 'nullable|array',
            'location_id' => 'nullable|array',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }
}