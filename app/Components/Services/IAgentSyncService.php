<?php

namespace App\Components\Services;

interface IAgentSyncService
{
    public function bulk($raw_datas, $webhook);
    
    public function process( $data, $webhook = null);

    public function transform( $data );

    
}