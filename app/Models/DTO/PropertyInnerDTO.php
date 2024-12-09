<?php

namespace App\Models\DTO;

class PropertyInnerDTO
{
    public $slug;
    public $ref; 
    public $webRef;
    public $marketStatus;
    public $expiry_date_time;
    public $marketType; 
    public $commercial; 
    public $region; 
    public $region_details; 
    public $locality;
    public $locality_details;
    public $propertyType;
    public $propertyType_details; 
    public $property_status; 
    public $property_status_details; 
    public $price;
    public $oldPrice;  
    public $premium;
    public $rentPeriod; 
    public $dateAvailable;
    public $priceOnRequest;  
    public $description;
    public $title;
    public $longDescription;
    public $specifications;
    public $itemsIncludedInPrice;
    public $soleAgents;
    public $propertyBlock;
    public $bedrooms;
    public $bathrooms;
    public $garage;
    public $contactDetails;
    public $isPropertyOfTheMonth;
    public $isFeatured;
    public $isHotProperty;
    public $dateOnMarket;
    public $dateOffMarket;
    public $datePriceReduced;
    public $virtualTourUrl;
    public $showOn3rdPartySites;
    public $pricesStartingFrom;
    public $hotPropertyTitle;
    public $area;
    public $plot_area;
    public $external_area;
    public $internal_area;
    public $weight;
    public $features;
    public $files;
    public $consultant;
    public $latitude;
    public $longitude;
    public $show_in_searches;
    public $three_d_walk_through;
    public $is_managed_property;
    public $project;
    public $project_details;
    public $created_at;
    public $updated_at;

    public $formatted_features;

    public function __construct(
        $slug,
        $ref,
        $webRef,
        $marketStatus,
        $expiry_date_time,
        $marketType,
        $commercial,
        $region,
        $region_details,
        $locality,
        $locality_details,
        $propertyType,
        $propertyType_details,
        $property_status,
        $property_status_details,
        $price,
        $oldPrice,
        $premium,
        $rentPeriod,
        $dateAvailable,
        $priceOnRequest,
        $description,
        $title,
        $longDescription,
        $specifications,
        $itemsIncludedInPrice,
        $soleAgents,
        $propertyBlock,
        $bedrooms,
        $bathrooms,
        $garage,
        $contactDetails,
        $isPropertyOfTheMonth,
        $isFeatured,
        $isHotProperty,
        $dateOnMarket,
        $dateOffMarket,
        $datePriceReduced,
        $virtualTourUrl,
        $showOn3rdPartySites,
        $pricesStartingFrom,
        $hotPropertyTitle,
        $area,
        $plot_area,
        $external_area,
        $internal_area,
        $weight,
        $features,
        $files,
        $consultant,
        $latitude,
        $longitude,
        $show_in_searches,
        $three_d_walk_through,
        $is_managed_property,
        $project,
        $project_details,
        $created_at,
        $updated_at,
    ) {
        $this->slug = $slug;
        $this->ref = $ref;
        $this->webRef = $webRef;
        $this->marketStatus = $marketStatus;
        $this->expiry_date_time = $expiry_date_time;
        $this->marketType = $marketType;
        $this->commercial = $commercial;
        $this->region = $region;
        $this->region_details = $region_details;
        $this->locality = $locality;
        $this->locality_details = $locality_details;
        $this->propertyType = $propertyType;
        $this->propertyType_details = $propertyType_details;
        $this->property_status = $property_status;
        $this->property_status_details = $property_status_details;
        $this->price = $price;
        $this->oldPrice = $oldPrice;
        $this->premium = $premium;
        $this->rentPeriod = $rentPeriod;
        $this->dateAvailable = $dateAvailable;
        $this->priceOnRequest = $priceOnRequest;
        $this->description = $description;
        $this->title = $title;
        $this->longDescription = $longDescription;
        $this->specifications = $specifications;
        $this->itemsIncludedInPrice = $itemsIncludedInPrice;
        $this->soleAgents = $soleAgents;
        $this->propertyBlock = $propertyBlock;
        $this->bedrooms = $bedrooms;
        $this->bathrooms = $bathrooms;
        $this->garage = $garage;
        $this->contactDetails = $contactDetails;
        $this->isPropertyOfTheMonth = $isPropertyOfTheMonth;
        $this->isFeatured = $isFeatured;
        $this->isHotProperty = $isHotProperty;
        $this->dateOnMarket = $dateOnMarket;
        $this->dateOffMarket = $dateOffMarket;
        $this->datePriceReduced = $datePriceReduced;
        $this->virtualTourUrl = $virtualTourUrl;
        $this->showOn3rdPartySites = $showOn3rdPartySites;
        $this->pricesStartingFrom = $pricesStartingFrom;
        $this->hotPropertyTitle = $hotPropertyTitle;
        $this->area = $area;
        $this->plot_area = $plot_area;
        $this->external_area = $external_area;
        $this->internal_area = $internal_area;
        $this->weight = $weight;
        $this->features = $features;
        $this->files = $files;
        $this->consultant = $consultant;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->show_in_searches = $show_in_searches;
        $this->three_d_walk_through = $three_d_walk_through;
        $this->is_managed_property = $is_managed_property;
        $this->project = $project;
        $this->project_details = $project_details;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->formatted_features = null;
    }

}
