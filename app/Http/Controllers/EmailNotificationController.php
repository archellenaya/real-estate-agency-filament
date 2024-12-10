<?php

namespace App\Http\Controllers;

use App\Components\Services\IEmailNotificationService;
use App\Constants\Components\NotificationTriggerValue;
use App\Models\EmailNotification;
use App\Models\EmailTemplate;
use App\Models\NotificationTrigger;
use App\Niu\Transformers\EmailNotificationTransformer;
use Illuminate\Http\Request;

class EmailNotificationController extends ApiController
{
    protected $emailNotificationTransformer;

    public function __construct(EmailNotificationTransformer $emailNotificationTransformer)
    {
        // parent::__construct();

        $this->emailNotificationTransformer = $emailNotificationTransformer;
    }

    public function index(Request $request)
    {
        $notification_trigger_id = NotificationTrigger::where('id', NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM)->first()->id;
        $email_template_id = EmailTemplate::where('notification_trigger_id', $notification_trigger_id)->first()->id;

        $limit = $request->get('limit') ?? '10';

        $email_notifications =  EmailNotification::where('email_template_id', $email_template_id)->paginate($limit);

        return $this->respondWithPagination($email_notifications, [
            'data' => $this->emailNotificationTransformer->transformCollection($email_notifications->all())
        ]);
    }
}
