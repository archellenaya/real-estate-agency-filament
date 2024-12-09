<?php

namespace App\Components\Services;

interface IBranchSyncService
{
    public function bulk($raw_datas, $webhook);

    public function process( $raw_data, $webhook = null);

    public function transform($data);
}