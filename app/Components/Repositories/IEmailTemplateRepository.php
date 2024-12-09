<?php

namespace App\Components\Repositories;

interface IEmailTemplateRepository
{
    public function getByNotificationTriggerId($notification_trigger_id);
}
