<?php

namespace App\Components\Repositories;

interface ILandlordRepository
{
    public function getAllTenants();

    public function addTenants($data);

    public function updateTenantAccessToken($data, $id);

    public function updateTenant($data, $id);

    public function getTenant($id);

    public function deleteTenant($id); 
}
