<?php

namespace App\Components\Repositories;

interface IPropertyAlertRepository
{
    public function getById($id);

    public function createPropertyAlert(
        $name,
        $type_id,
        $property_type_id,
        $location_id,
        $min_price,
        $max_price
    );

    public function getAll($per_page);

    public function updatePropertyAlert(
        $id,
        $name,
        $type_id,
        $property_type_id,
        $location_id,
        $min_price,
        $max_price
    );
}
