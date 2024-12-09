<?php

namespace App\Components\Services\Impl;

use App\Components\Services\ILandlordService; 
use App\Components\Repositories\ILandlordRepository; 
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode; 

class LandlordService implements ILandlordService {

    private $_landlordRepository;

    public function __construct(ILandlordRepository $landlordRepository)
    {
        $this->_landlordRepository = $landlordRepository;
    }

    public function getAllTenants()
    {
        return $this->_landlordRepository->getAllTenants();
    }
 
    public function addTenants($data)
    { 
        return $this->_landlordRepository->addTenants($data);
    }

    public function updateTenantAccessToken($data, $id) 
    {
        return $this->_landlordRepository->updateTenantAccessToken($data, $id);
    }

    public function updateTenant($data, $id) 
    {
        return $this->_landlordRepository->updateTenant($data, $id);
    }

    public function getTenant($id)
    {
        return $this->_landlordRepository->getTenant($id);
    }

    public function deleteTenant($id) 
    {
        return $this->_landlordRepository->deleteTenant($id);
    }
}
