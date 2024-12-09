<?php

namespace App\Components\Services\Impl;

use App\Components\Repositories\IEmailNotificationRepository;
use App\Components\Repositories\IEmailTemplateRepository;
use App\Components\Repositories\INotificationTriggerRepository;
use App\Components\Services\IEmailNotificationService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use Illuminate\Support\Facades\Log;

class EmailNotificationService implements IEmailNotificationService
{
    private $_emailTemplateRepository;
    private $_notificationTriggerRepository;
    private $_emailNotificationRepository;

    public function __construct(
        IEmailTemplateRepository $emailTemplateRepository,
        INotificationTriggerRepository $notificationTriggerRepository,
        IEmailNotificationRepository $emailNotificationRepository
    ) {
        $this->_emailTemplateRepository = $emailTemplateRepository;
        $this->_notificationTriggerRepository = $notificationTriggerRepository;
        $this->_emailNotificationRepository = $emailNotificationRepository;
    }

    public function createEmailNotification($properties, $trigger_value, $recipient, $attachments = null)
    {
     
        $notification_trigger = $this->_notificationTriggerRepository->getByValue($trigger_value);

        if (empty($notification_trigger)) {
            throw new ProcessException(
                ProcessExceptionMessage::NOTIFICATION_TRIGGER_DOES_NOT_EXIST,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $email_template = $this->_emailTemplateRepository->getByNotificationTriggerId($notification_trigger->id);

        if (empty($email_template)) {
            throw new ProcessException(
                ProcessExceptionMessage::EMAIL_TEMPLATE_DOES_NOT_EXIST,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $interpolated_subject = $this->interpolate($properties, $email_template->subject);
        $recipient            = join(', ', $recipient);
       
        //before interpolating the template found within the DB , attempt to find the template inside the views folder, using laravel native interpolating capabilities instead of reinventing the wheel ....
        //note this is a workaround around the existing email template process that stores templates directly in DB, complexing the whole notification process, consider simplifying this or rewriting it all together 
        try {
            
            $interpolated_body = view('emails.'.tenant('id').'.'.strtolower($notification_trigger->name),$properties)->render();

        } catch(\Exception $e) { 
            Log::error("Error while generating tenant specific view email: ".strtolower($notification_trigger->name).', exception: '.$e->getMessage());

            try{
      
                  //template with tenant_id not found, use the default email template
                $interpolated_body = view('emails.'.strtolower($notification_trigger->name),$properties)->render();

            } catch(\Exception $e) {
                Log::error("Error while generating default view email: ".strtolower($notification_trigger->name).', exception: '.$e->getMessage());
                //template not found, use the email template defined in DB
     
                $interpolated_body = $this->interpolate($properties, $email_template->body);
            } 
        }
    
        $notification = $this->_emailNotificationRepository->createEmailNotification(
            $email_template->id,
            $interpolated_subject,
            $interpolated_body,
            $recipient,
            $attachments
        );

        return $notification;
    }


    private function interpolate_template($properties, $text)
    {
        foreach($properties as $key => $val) {
            $pattern = '/{{'. $key .'}}/i';
            $text = preg_replace($pattern, $val, $text);
        }

        return $text;
    }

    private function interpolate($properties, $text)
    {
        foreach($properties as $key => $val) {
            if(empty($val) === true || is_string($val) === false){
                continue;
            }
            $pattern = '/{{'. $key .'}}/i';
            $text = preg_replace($pattern, $val, $text);
        }

        return $text;
    }

    public function getUnSentEmailNotification($limit = 100)
    {
        return $this->_emailNotificationRepository->getUnSentEmailNotification($limit);
    }

    public function getEmailNotification($id)
    {
        $email_notification = $this->_emailNotificationRepository->getEmailNotification($id);

        if (empty($email_notification)) {
            throw new ProcessException(
                ProcessExceptionMessage::EMAIL_NOTIFICATION_DOES_NOT_EXIST,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $email_notification;
    }

    public function getAll()
    {   
        return $this->_emailNotificationRepository->getAll();
    }

    public function setEmailAsSent($id)
    {
       $email_notification = $this->getEmailNotification($id);

       return $this->_emailNotificationRepository->setEmailAsSent($email_notification->id);
    }
}
