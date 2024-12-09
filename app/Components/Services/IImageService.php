<?php

namespace App\Components\Services;

interface IImageService
{
   public function getConsultant($filename, $width = null, $height = null, $trials =1);

   public function getProperty($filename, $type = '', $width = null, $height = null);

   public function getPublicImage($filename, $width, $height);

   public function getUser($filename, $width = null, $height = null);
}
