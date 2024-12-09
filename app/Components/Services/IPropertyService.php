<?php

namespace App\Components\Services;

interface IPropertyService
{
    public function createPropertyWithRefOnly($reference);

    public function getPropertyByRef($reference);

    public function detachUserFromProperty($user, $property);  

    public function search($parameter = [], $limit = 10, $sort_order = 'latest');

    public function reducedPrice($limit = 10, $days_limit = 7);

    public function increasedPrice($limit = 10, $days_limit = 7);

    public function getPropertyImageByRef($reference);

    public function getBySlug($slug);

    public function getPropertyThumnail($property);

    public function updateCreatePropertyXML();

    public function getPropertyXML();
}