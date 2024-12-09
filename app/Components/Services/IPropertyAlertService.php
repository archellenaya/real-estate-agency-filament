<?php

namespace App\Components\Services;

interface IPropertyAlertService
{
    public function getPropertyAlert($id);

    public function createNewPropertyAlert(
        $name,
        $type_name,
        $property_type_id = [],
        $location_id = [],
        $min_price,
        $max_price
    );

    public function getPropertyAlertDTO($id);

    public function getAllPropertyAlerts($per_page);

    public function updatePropertyAlert(
        $id,
        $name,
        $type_name,
        $property_type_id = [],
        $location_id = [],
        $min_price,
        $max_price
    );

    public function deletePropertyAlert($id);
}