<?php

namespace App\Components\Repositories;

interface INotificationTriggerRepository
{
    public function getByValue($value);
}
