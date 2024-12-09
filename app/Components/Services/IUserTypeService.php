<?php

namespace App\Components\Services;

interface IUserTypeService
{
    public function getUserTypeById($id);

    public function getUserTypeByType($type);

    public function getAll();
}