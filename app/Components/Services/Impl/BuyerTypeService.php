<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IBuyerTypeService;
use App\Components\Repositories\IBuyerTypeRepository;


class BuyerTypeService implements IBuyerTypeService {

    private $_buyerTypeRepository;

    public function __construct(IBuyerTypeRepository $buyerTypeRepository)
    {
        $this->_buyerTypeRepository = $buyerTypeRepository;
    }

    public function getBuyerTypeList() 
    {
        return $this->_buyerTypeRepository->getList();
    }
}
