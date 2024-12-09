<?php

namespace App\Components\Repositories;

interface IPropertyRepository
{
    public function createPropertyWithRefOnly($reference);

    public function getPropertyByRef($reference);

    public function detachUser($user, $property);

    public function getPropertyOldId($id);

    public function createProperty($data); //old property api5

    public function updateProperty($reference, $data); //old property api5

    public function updatePropertyByRef($reference, $data);

    public function updatePropertyByOldId($id, $data);

    public function getPropertyByRefID($reference); //old property api5
    
    public function getPropertyByNodeID($node_id); //nodeid of drupal = old_id of api

    public function search($parameter = [], $limit = 10, $sort_order = 'latest');

    public function getAllProperties();

    public function reducedPrice($limit = 10, $days_limit = 7);

    public function increasedPrice($limit = 10, $days_limit = 7);

    public function getPropertyBasicDataByRef($reference);
    
    public function getPropertyImageByRef($reference);

    public function getBySlug($slug);

    public function getAllPropertiesNolimit();
}
