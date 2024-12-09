<?php 

namespace App\Components\Repositories;

interface ITypeRepository
{
    public function getById($id);

    public function getByName($name);

    public function getAll();
}