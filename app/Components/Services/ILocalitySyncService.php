<?php

namespace App\Components\Services;

interface ILocalitySyncService
{
    public function bulk($raw_datas, $webhook);

    public function process( $raw_data, $webhook);

    public function transform($data);
}