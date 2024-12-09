<?php

namespace App\Components\Repositories;

interface IFormRepository
{
    public function processForm($interpolation_properties, $form_type, $recipients );

    public function getLists($form_type);
}
