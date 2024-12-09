<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IPropertyAlertRepository;
use App\Models\PropertyAlert;

class PropertyAlertRepository implements IPropertyAlertRepository
{
    public function getById($id) 
    {
        return PropertyAlert::find($id);
    }

    public function createPropertyAlert(
        $name,
        $type_id,
        $property_type_id,
        $location_id,
        $min_price,
        $max_price
    ) 
    {
        return PropertyAlert::create([
            'name' => $name,
            'type_id' => $type_id,
            'property_type_id' => $property_type_id,
            'location_id' => $location_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'user_id' => auth()->user()->id
        ]);
    }

    public function getAll($per_page)
    {
        return PropertyAlert::latest()
            ->where('user_id',auth()->user()->id)
            ->paginate($per_page);
    }

    public function updatePropertyAlert(
        $id,
        $name,
        $type_id,
        $property_type_id,
        $location_id,
        $min_price,
        $max_price
    ) 
    {
        return PropertyAlert::where('id', $id)->update([
            'type_id' => $type_id,
            'name' => $name,
            'property_type_id' => $property_type_id,
            'location_id' => $location_id,
            'min_price' => $min_price,
            'max_price' => $max_price
        ]);
    }
}
