<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\INotificationTriggerRepository;
use App\Models\NotificationTrigger;

class NotificationTriggerRepository implements INotificationTriggerRepository
{
    public function getByValue($value)
    {
        return NotificationTrigger::where('value', $value)->first();
    }
}
