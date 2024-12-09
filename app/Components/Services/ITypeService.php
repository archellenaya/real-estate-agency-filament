<?php 

namespace App\Components\Services;

interface ITypeService
{
    public function getById($id);

    public function getByName($name);

    public function getAll();
}