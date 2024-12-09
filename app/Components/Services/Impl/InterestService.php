<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IInterestService;
use App\Components\Repositories\IInterestRepository;
use Illuminate\Support\Facades\Auth;

class InterestService implements IInterestService {

    private $_interestRepository;

    public function __construct(IInterestRepository $interestRepository)
    {
        $this->_interestRepository = $interestRepository;
    }

    public function getInterestList() 
    {
        return $this->_interestRepository->getList();
    }

}
