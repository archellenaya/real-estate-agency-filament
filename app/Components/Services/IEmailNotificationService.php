<?php

namespace App\Components\Services;

interface IEmailNotificationService
{
    public function createEmailNotification($properties, $subject, $recipient, $attachments = null);

    public function getUnSentEmailNotification($limit = 100);

    public function getEmailNotification($id);

    public function getAll();

    public function setEmailAsSent($id);

}
