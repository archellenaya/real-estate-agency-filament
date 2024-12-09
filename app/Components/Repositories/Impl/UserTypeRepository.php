<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IUserTypeRepository;
use App\Models\UserType;

class UserTypeRepository implements IUserTypeRepository
{
    public function getUserTypeById($id) 
    {
        return UserType::find($id);
    }

    public function getUserTypeByType($type) 
    {
        return UserType::where('type', $type)->first();
    }

    public function getAll() 
    {
        return UserType::all();
    }
}
