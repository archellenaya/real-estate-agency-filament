<?php 

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\ITypeRepository;
use App\Models\Type;

class TypeRepository implements ITypeRepository
{
    public function getById($id)
    {
        return Type::find($id);
    }

    public function getByName($name)
    {
        return Type::where('name', $name)->first();
    }

    public function getAll()
    {
        return Type::all();
    }
}