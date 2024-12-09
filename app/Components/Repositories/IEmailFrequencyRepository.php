<?php 

namespace App\Components\Repositories;

interface IEmailFrequencyRepository
{
    public function getById($id);

    public function getByName($name);

    public function getAll();
}