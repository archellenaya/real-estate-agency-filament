<?php

namespace App\Components\Passive;

class Sentry 
{
    public static function reportError($exception)
    {
        app('sentry')->captureException($exception);
    }
}