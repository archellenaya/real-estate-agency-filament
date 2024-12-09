<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IEmailNotificationRepository;
use App\Models\EmailNotification;
use Carbon\Carbon;

class EmailNotificationRepository implements IEmailNotificationRepository
{
    public function getUnSentEmailNotification($limit = 100)
    {
        return EmailNotification::where('sent', '!=', 1)->where('recipient', '!=', '')->limit($limit)->get();
    }

    public function getEmailNotification($id)
    {
        return EmailNotification::find($id);
    }
    
    public function getAll()
    {
        return EmailNotification::all();
    }

    public function setEmailAsSent($id)
    {
        return EmailNotification::find($id)->update([
            'sent' => 1,
            'date_sent' => Carbon::now()
        ]);
    }

    public function createEmailNotification($email_template_id, $subject, $body, $recipient, $attachments = null)
    {
        if (is_array($recipient) === true) {
            $recipient = implode(',', $recipient);
        }

        return EmailNotification::create([
            'email_template_id' => $email_template_id,
            'subject' => $subject,
            'body' => $body,
            'recipient' => $recipient,
            'sent' => false,
            'attachments' => isset($attachments) ? json_encode($attachments) : null
        ]);
    }
}
