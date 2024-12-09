<?php

namespace App\Components\Services;

interface IFormService
{
    public function create($data, $form_type);

    public function getLists($form_type);
}