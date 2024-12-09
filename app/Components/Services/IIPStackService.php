<?php

namespace App\Components\Services;

interface IIPStackService
{
    public function getIPInformation(string $ip);
}
