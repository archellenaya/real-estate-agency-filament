<?php

namespace App\Models\DTO;

class PropertyDTO
{
    public $ref; 
    public $slug;
    public $area;
    public $files;
    public $price;
    public $rentPeriod; 
    public $webRef; 
    public $garage; 
    public $locality; 
    public $bedrooms;
    public $bathrooms;
    public $soleAgents;
    public $consultant; 
    public $marketType; 
    public $marketStatus; 
    public $region_details;
    public $priceOnRequest;  
    public $locality_details;
    public $propertyType_details; 
    public $property_status_details;
    public $dateAvailable;  
    public $tags;

    public function __construct(
        $ref, 
        $tags,
        $slug, 
        $area,
        $files,
        $price,
        $rentPeriod,
        $webRef,
        $garage, 
        $locality,
        $bedrooms, 
        $bathrooms, 
        $soleAgents,
        $consultant, 
        $marketType, 
        $marketStatus, 
        $region_details,
        $priceOnRequest,   
        $locality_details,
        $propertyType_details, 
        $property_status_details,
        $date_available_field,
        
    ) {
        $this->ref = $ref;
        $this->tags = $tags; 
        $this->slug = $slug; 
        $this->area = $area;
        $this->files = $files;
        $this->price = $price;
        $this->rentPeriod = $rentPeriod; 
        $this->webRef = $webRef; 
        $this->garage = $garage;
        $this->locality = $locality;
        $this->bedrooms = $bedrooms;
        $this->bathrooms = $bathrooms;
        $this->soleAgents = $soleAgents;
        $this->consultant = $consultant;
        $this->marketType = $marketType; 
        $this->marketStatus = $marketStatus; 
        $this->region_details = $region_details;
        $this->priceOnRequest = $priceOnRequest;  
        $this->locality_details = $locality_details;
        $this->propertyType_details = $propertyType_details; 
        $this->property_status_details = $property_status_details; 
        $this->dateAvailable = $date_available_field;  
    }
}
