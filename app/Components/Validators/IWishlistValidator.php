<?php

namespace App\Components\Validators;

interface IWishlistValidator
{
    public function validateSavingFavoriteProperty($reference);

    public function validateUpdatePropertyAlert(array $data);
}