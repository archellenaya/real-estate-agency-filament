<?php

namespace App\Components\Services;

interface ICloudflareService
{ 
   public function purgeUrls($urls); 

   public function getPropertyImageUrls($property_id);

   public function getConsultantImage($consultant_id);

   public function purgePropertyByReference($reference);
}