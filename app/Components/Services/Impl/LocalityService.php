<?php

namespace App\Components\Services\Impl;

use App\Components\Services\ILocalityService;
use App\Components\Services\Impl\SyncUtilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Repositories\ILocalityRepository;
use App\Models\DataImport;
use Exception;

class LocalityService  implements ILocalityService
{

    private $_localityRepository;

    public function __construct(ILocalityRepository $localityRepository)
    {
        $this->_localityRepository  = $localityRepository;
    }

    public function getRegions($names)
    {
        return $this->_localityRepository->getRegions($names);
    }
}
