<?php

namespace App\Components\Services\Impl;

use App\Components\Passive\Sentry;
use App\Components\Services\ICommandService;
use App\Components\Services\IEmailNotificationService;
use App\Components\Services\IEmailService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CommandService implements ICommandService {

    private $_emailService;
    private $_emailNotificationService;
    
    public function __construct(
        IEmailService $emailService,
        IEmailNotificationService $emailNotificationService
    )
    {
        $this->_emailService = $emailService;
        $this->_emailNotificationService = $emailNotificationService;
    }

    public function sendEmailNotifications(?Command $command=null)
    {
		$this->logInfo('Start sending emails',$command,false);

      	$email_notifications = $this->_emailNotificationService->getUnSentEmailNotification();

		foreach ($email_notifications as $email_notification) {

			$this->logInfo('Sending email to '. $email_notification->recipient,$command,true);

			try {
				DB::transaction(function() use ($email_notification) {
					$recipients = explode(', ', $email_notification->recipient);
				
					$this->_emailService->send(
						$email_notification->subject,
						$email_notification->body,
						$recipients,
						$email_notification->attachments
					);

					$this->_emailNotificationService->setEmailAsSent($email_notification->id);
				});

				$this->logInfo('Email sent to '. $email_notification->recipient,$command);

			} catch (Exception $e) {
				Sentry::reportError($e);

				$this->logInfo('Failed to send email to '. $email_notification->recipient.", exception: ".$e->getMessage(),$command);
				// Don't throw error so that it won't stop sending email to other recipients
			}
		}

		$this->logInfo('Finished sending emails',$command,false);
    }


	private function logInfo(string $message,?Command $command=null,bool $createLog=true):void{
		if($createLog === true){
			Log::info($message);
		}
		if($command === null){
			return;
		}
		$command->info($message);
	}
}
