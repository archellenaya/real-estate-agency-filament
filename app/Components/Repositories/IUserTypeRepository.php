<?php

namespace App\Components\Repositories;

interface IUserTypeRepository
{
    public function getUserTypeById($id);

    public function getUserTypeByType($type);

    public function getAll();
}
