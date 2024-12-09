<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IEmailTemplateRepository;
use App\Models\EmailTemplate;

class EmailTemplateRepository implements IEmailTemplateRepository
{
    public function getByNotificationTriggerId($notification_trigger_id)
    {
        return EmailTemplate::where('notification_trigger_id', $notification_trigger_id)->first();
    }
}
