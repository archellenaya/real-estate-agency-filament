<?php

namespace App\Models\DTO;

class PropertyEnqueryDTO
{
    public $property_id;
    public $fullname;
    public $email;
    public $prefix_phone_number;
    public $phone_number;
    public $comment;
    public $want_to_see_property;
    public $date;
    public $time;

    public function __construct(
        $property_id, 
        $fullname, 
        $email,
        $prefix_phone_number,
        $phone_number,
        $comment,
        $want_to_see_property, 
        $date,
        $time
    )
    {
        $this->property_id = $property_id;
        $this->fullname = $fullname;
        $this->email = $email;
        $this->prefix_phone_number = $prefix_phone_number;
        $this->phone_number = $phone_number;
        $this->comment = $comment;
        $this->want_to_see_property = $want_to_see_property;
        $this->date = $date;
        $this->time = $time;
    }
}
