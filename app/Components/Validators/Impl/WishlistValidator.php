<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\IWishlistValidator;
use App\Components\Validators\Impl\PropertyValidator;
use Illuminate\Support\Facades\Validator;

class WishlistValidator implements IWishlistValidator
{
    private $_propertyValidator;

    public function __construct()
    {
        $this->_propertyValidator = new PropertyValidator();
    }

    public function validateSavingFavoriteProperty($reference)
    {
        return $this->_propertyValidator->validatePropertyReference($reference);
    }


    public function validateUpdatePropertyAlert(array $data){
        $rules = [
            "propertyRef"  => 'required',
            "alertOn"      => 'required|in:1,0'
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }
}