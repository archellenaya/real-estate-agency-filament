<?php

namespace App\Components\Services;

interface ISocialAuthenticationService
{
    public function authenticateProvider($provider, $code);
}