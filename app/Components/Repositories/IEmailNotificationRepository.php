<?php

namespace App\Components\Repositories;

interface IEmailNotificationRepository
{
    public function getUnSentEmailNotification($limit = 100);

    public function getEmailNotification($id);

    public function getAll();

    public function setEmailAsSent($id);

    public function createEmailNotification($email_template_id, $subject, $body, $recipient, $attachments = null);
}
