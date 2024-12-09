<?php

namespace App\Models\DTO;

class BranchDTO
{
    public $id;
    public $name;
    public $slug;
    public $email;
    public $contact_number;
    public $address;
    public $coordinates;
    public $display_order;

    public function __construct(
        $id, 
        $name, 
        $slug,
        $email,
        $contact_number,
        $address,
        $coordinates,
        $display_order
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->email = $email;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->coordinates = $coordinates; 
        $this->display_order = $display_order;  
    }
}
