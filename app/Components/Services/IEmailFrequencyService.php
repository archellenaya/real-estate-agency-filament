<?php

namespace App\Components\Services;

interface IEmailFrequencyService
{
    public function getById($id);

    public function getByName($name);

    public function getAll();
}