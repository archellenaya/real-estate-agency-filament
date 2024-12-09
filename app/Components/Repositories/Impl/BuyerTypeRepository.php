<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IBuyerTypeRepository;
use App\Models\BuyerType;
use Illuminate\Support\Facades\Log;
class BuyerTypeRepository implements IBuyerTypeRepository
{

    public function getList() 
    {
        return BuyerType::all();
    }
}
