<?php

namespace App\Components\Services;

interface IPropertySyncService
{
    public function bulk($raw_datas, $webhook);
    
    public function process( $data, $webhook = null);

    public function transform( $data );

    public function getAllProperties();
}