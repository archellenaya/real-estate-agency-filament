<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IInterestRepository;
use App\Models\Interest;

class InterestRepository implements IInterestRepository
{
    public function getList() 
    {
        return Interest::all();
    }
}
