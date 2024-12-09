<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IWishlistService;
use App\Components\Services\IPropertyService;
use App\Components\Services\IUserService;
use App\Models\Property;
use App\Models\User;

class WishlistService implements IWishlistService {

    private $_propertyService;
    private $_userService;
    private $_apiV5Service;

    public function __construct(IPropertyService $propertyService, IUserService $userService,APIv5Service $apiV5Service)
    {
        $this->_propertyService = $propertyService;
        
        $this->_userService = $userService;
        
        $this->_apiV5Service = $apiV5Service;
    } 

    public function addToList($property_ref)
    {
        $user = $this->_userService->getAuthUser();

        $this->_userService->addFavoriteProperty($user, $property_ref);
    }

    public function removeToList($property_ref)
    {
        $user = $this->_userService->getAuthUser();

        $this->_userService->removeFavoriteProperty($user, $property_ref);
    }

    public function getList(bool $get_alert_status=false) 
    {
        $user = $this->_userService->getAuthUser();

        $properties = [];
       
        $property_saved = $this->_userService->wishLists($user);
        
        foreach( $property_saved  as $property) {
            
            if($get_alert_status === true){
                $alerts_on = $property && isset($property->pivot) && isset($property->pivot->alerts_on) ? $property->pivot->alerts_on : 0; 
                array_push($properties, [
                    'ref' => $property->property_ref_field,
                    'alerts_on' => $alerts_on === 1 ? true : false
                ]);
            }else{
                array_push($properties, $property->property_ref_field);
            }
        }

        return $properties;
    }

    public function clearList()
    {
        $this->_userService->clearWishLists();
    }

    public function inList($reference)
    {
        $wishlist = $this->getList();
        if(in_array($reference,$wishlist ))
            return true;
        else
            return false;
    }

    public function updatePropertyAlert(User $user, Property $property,bool $alertOn){
        return $this->_userService->updateWishListPropertyAlert($user,$property,$alertOn);
    }

}
