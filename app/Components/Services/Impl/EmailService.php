<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IEmailService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Mail\MailTemplate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService implements IEmailService
{

    public function send($subject, $body, $recipient, $attachments = null)
    {

        if (is_string($recipient)) {
            $recipient = explode(',', $recipient);
            
        }elseif(count($recipient) === 1 && str_contains($recipient[0],',') ){
            $recipient = explode(',', $recipient[0]);
        }

        $recipientsCCFormatted = array();
        $recipientMain         = null;
        foreach ($recipient as $recipientEmail) { 
            $recipientEmail = trim($recipientEmail);
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Invalid Email Encountered when sending email: $recipientEmail, Skipped, metadata:" . json_encode(
                    [
                        'subject'           => $subject,
                        'recipients'        => $recipient,
                        'recipientsMain'    => $recipientMain,
                        'recipientsCC'      => ($recipientsCCFormatted)
                    ]
                ));
                continue;
            }

            if ($recipientMain === null) {
                $recipientMain = $recipientEmail;
            } else {
                $recipientsCCFormatted[] = $recipientEmail;
            }
        }


        if(empty($recipientMain) === true){
            Log::error("Sending mail, recipientMain is empty: " . json_encode(
                [
                    'subject'           => $subject,
                    'recipients'        => $recipient,
                    'recipientsMain'    => $recipientMain,
                    'recipientsCC'      => ($recipientsCCFormatted),
                ]
            ));
            throw new ProcessException(
                ProcessExceptionMessage::ERROR_IN_SENDING_EMAIL,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        

        try {   
            $bcc_recipients = !empty(config("mail.bcc_super_admin_address")) ? array_map('trim', explode(',', config("mail.bcc_super_admin_address"))):[];
            
            Mail::send([], [], function ($message) use ($body, $subject, &$recipientMain, $recipientsCCFormatted, $attachments, $bcc_recipients) {
                $message->to($recipientMain)
                    ->cc($recipientsCCFormatted)
                    ->bcc($bcc_recipients)
                    ->subject($subject)
                    ->html($body, 'text/html');
                if (is_array($attachments) === true) {
                    foreach ($attachments as $file) {
                        $message->attach($file);
                    }
                }
            });  
        } catch (Exception $e) {
            Log::error("Sending mail: " . json_encode(
                [
                    'subject'           => $subject,
                    'recipients'        => $recipient,
                    'recipientsMain'    => $recipientMain,
                    'recipientsCC'      => ($recipientsCCFormatted),
                    'exception'         => $e->getMessage()
                ]
            ));

            throw new ProcessException(
                ProcessExceptionMessage::ERROR_IN_SENDING_EMAIL,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
