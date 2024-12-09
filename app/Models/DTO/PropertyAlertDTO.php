<?php 

namespace App\Models\DTO;

class PropertyAlertDTO
{
    public $id;
    public $type;
    public $property_type_id;
    public $location_id;
    public $min_price;
    public $max_price;

    public function __construct(
        $id,
        $name,
        $type,
        $property_type_id,
        $location_id,
        $min_price,
        $max_price
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->property_type_id = $property_type_id;
        $this->location_id = $location_id;
        $this->min_price = $min_price;
        $this->max_price = $max_price;
    } 
}