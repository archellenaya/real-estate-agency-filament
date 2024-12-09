<?php

namespace App\Components\Repositories\Impl;

use App\Locality;
use App\Models\Region;
use App\Constants\Components\Regions;
use App\Constants\Components\RegionValues;
use App\Components\Repositories\ILocalityRepository;

class LocalityRepository implements ILocalityRepository
{
    private $_regions =  [
        RegionValues::NORTH                 => Regions::NORTH,
        RegionValues::SOUTH                 => Regions::SOUTH,
        RegionValues::CENTRAL               => Regions::CENTRAL,
        RegionValues::GOZO                  => Regions::GOZO,
        RegionValues::SLIEMA_AND_ST_JULIANS => Regions::SLIEMA_AND_ST_JULIANS,
        RegionValues::SICILY                => Regions::SICILY
    ];

    public function createLocality($data)
    {
        return Locality::create($data);
    }

    public function updateLocality($id, $data)
    {
        return Locality::where("id", $id)->update($data);
    }

    public function getLocalityByOldID($id)
    {
        return Locality::where("old_id", $id)->first();
    }

    public function getRegions($names = [])
    {
        return Region::when(!empty($names), function ($query) use ($names) {
            $query->whereIn('description', $names);
        })->get();
    }

    public function getRegion($id)
    {
        return Region::where("id", $id)->first();
    }
}
