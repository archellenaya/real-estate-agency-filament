<?php

namespace App\Components\Repositories;

interface ILocalityRepository
{
    public function createLocality($data);

    public function updateLocality($id, $data);

    public function getLocalityByOldID($id);

    public function  getRegions($names);
}
