<?php

namespace App\Components\Services;

interface IProjectSyncService 
{
    public function bulk($raw_datas, $webhook);

    public function process( $data, $webhook);

    public function transform( $data );
}