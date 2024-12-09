<?php

namespace Database\Seeders;

use App\Constants\Components\NotificationTriggerName;
use App\Models\EmailTemplate;
use App\Models\NotificationTrigger;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_USER_REGISTRATION)->first()->id,
                'body'                    => '<p>Please click on the <a href="{{user_activation_link}}">link</a> to activate your account.</p>',
                'subject'                 => 'Activation for User {{email}}'
            ], [
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_RESET_PASSWORD)->first()->id,
                'body'                    => '<p>Please use the following code to reset your password : {{code}}</p>
                                             <p>or Click on the following button</p>
                                             <a class="button button-primary" href="{{change_password_link}}">Reset Password Link</a>',
                'subject'                 => 'Reset Password for User {{email}}'
            ], [
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_CAREER_FORM)->first()->id,
                'body'                    => '<p><b>Fullname:</b> {{fullname}}</p>
                                              <p><b>Prefix:</b> {{prefix_phone_number}}</p>
                                              <p><b>Phone:</b> {{phone_number}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Position:</b> {{position}}</p>
                                              <p>Attached CV and Cover Letter.</p>',
                'subject'                 => 'Career Form - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_PROPERTY_QUERY_FORM)->first()->id,
                'body'                    => '<p><b>Fullname:</b> {{fullname}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Prefix:</b> {{prefix_phone_number}}</p>
                                              <p><b>Phone:</b> {{phone_number}}</p>
                                              <p><b>Comment:</b> {{comment}}</p>
                                              <p><b>I want to see this property:</b> {{want_to_see_property}}</p>
                                              <p><b>Date:</b> {{date}}</p>
                                              <p><b>Time:</b> {{time}}</p>',
                'subject'                 => 'Property #{{property_id}} Enquiry - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_PROPERTY_REGISTRATION_FORM)->first()->id,
                'body'                    => '<p><b>Name:</b> {{name}}</p>
                                              <p><b>Surname:</b> {{surname}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Prefix:</b> {{prefix_phone_number}}</p>
                                              <p><b>Phone:</b> {{phone_number}}</p>
                                              <p><b>Where is the property located? :</b> {{location}}</p>
                                              <p><b>Residential:</b> {{residential}}</p>
                                              <p><b>Bedrooms:</b> {{number_of_bedroom}}</p>
                                              <p><b>Bathrooms:</b> {{number_of_bathroom}}</p>
                                              <p><b>Are you looking to sell, let or both:</b> {{purpose}}</p>
                                              <p><b>Property Description:</b> {{property_description}}</p>',
                'subject'                 => 'Quick Submission Form - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_PROPERTY_QUERY_FORM_FE_USER)->first()->id,
                'body'                    => '<p><b>Name:</b> {{name}}</p>
                                              <p><b>Surname:</b> {{surname}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Prefix:</b> {{prefix_phone_number}}</p>
                                              <p><b>Phone:</b> {{phone_number}}</p>
                                              <p><b>Where is the property located? :</b> {{location}}</p>
                                              <p><b>Residential:</b> {{residential}}</p>
                                              <p><b>Bedrooms:</b> {{number_of_bedroom}}</p>
                                              <p><b>Bathrooms:</b> {{number_of_bathroom}}</p>
                                              <p><b>Are you looking to sell, let or both:</b> {{purpose}}</p>
                                              <p><b>Property Description:</b> {{property_description}}</p>',
                'subject'                 => 'Quick Submission Inquiry Form - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_CONTACT_US_FORM)->first()->id,
                'body'                    => '<p><b>Fullname:</b> {{fullname}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Prefix:</b> {{prefix_phone_number}}</p>
                                              <p><b>Phone:</b> {{phone_number}}</p>
                                              <p><b>Country:</b> {{country}}</p>
                                              <p><b>Message:</b> {{message}}</p>',
                'subject'                 => 'Contact Us Form - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_CREATE_AGENT_REVIEW_FORM)->first()->id,
                'body'                    => '<p><b>Fullname:</b> {{fullname}}</p>
                                              <p><b>Email:</b> {{email}}</p>
                                              <p><b>Country:</b> {{country}}</p>
                                              <p><b>Message:</b> {{review_comment}}</p>',
                'subject'                 => 'Agent Review - {{fullname}}'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_MORE_INFO_PROPERTIES)->first()->id,
                'body'                    => '<p><b>Message:</b></p>
                                              <p>{{message}}</p>
                                              <p><b>Properties:</b></p>
                                              <p>{{property_links}}</p>',
                'subject'                 => 'Request for More Information of the Properties'
            ], [
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_MORE_INFO_PROPERTIES_FE_USER)->first()->id,
                'body'                    => '<p><b>Message:</b></p>
                                              <p>{{message}}</p>
                                              <p><b>Properties:</b></p>
                                              <p>{{property_links}}</p>',
                'subject'                 => 'Request for More Information of the Properties'
            ]
            ,[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_SHARE_SAVED_PROPERTIES)->first()->id,
                'body'                    => '<p><b>Message:</b></p>
                                              <p>{{message}}</p>
                                              <p><b>Saved Properties:</b></p>
                                              <p>{{property_links}}</p>',
                'subject'                 => 'Shared Saved Properties'
            ],[
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_PROPERTY_CHANGE_ALERT)->first()->id,
                'body'                    => '<p><b>Message:</b></p>
                                              <p>{{message}}</p>
                                              <p><b>Property Alert:</b></p>
                                              <p>{{property_links}}</p>',
                'subject'                 => 'Property Alert'
            ],
            [
                'notification_trigger_id' => NotificationTrigger::where("name", NotificationTriggerName::ON_USER_DEACTIVATION)->first()->id,
                'body'                    => '<p>We are sorry to see you go</p>',
                'subject'                 => 'User Deactivation'
            ]
        ];

        foreach ($data as $item)
        {
            $table = new EmailTemplate;
            $row = $table->where('notification_trigger_id', $item['notification_trigger_id'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("Email Template - %s has been added \n", $item['subject']);
            } else {
                $row->update($item);
            }
        }
    }
}
