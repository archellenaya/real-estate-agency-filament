<?php

namespace App\Components\Services;

interface IEmailService
{
    public function send($subject, $body, $recipient, $attachments = null);
}