<?php

namespace Database\Seeders;

use App\Constants\Components\NotificationTriggerName;
use App\Constants\Components\NotificationTriggerValue;
use App\Models\NotificationTrigger;
use Illuminate\Database\Seeder;

class NotificationTriggerSeeder extends Seeder
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
                'name' => NotificationTriggerName::ON_USER_REGISTRATION,
                'value' => NotificationTriggerValue::ON_USER_REGISTRATION,
            ], [
                'name' => NotificationTriggerName::ON_RESET_PASSWORD,
                'value' => NotificationTriggerValue::ON_RESET_PASSWORD,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_CAREER_FORM,
                'value' => NotificationTriggerValue::ON_CREATE_CAREER_FORM,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_PROPERTY_REGISTRATION_FORM,
                'value' => NotificationTriggerValue::ON_CREATE_PROPERTY_REGISTRATION_FORM,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_PROPERTY_QUERY_FORM,
                'value' => NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_PROPERTY_QUERY_FORM_FE_USER,
                'value' => NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM_FE_USER,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_CONTACT_US_FORM,
                'value' => NotificationTriggerValue::ON_CREATE_CONTACT_US_FORM,
            ], [
                'name' => NotificationTriggerName::ON_CREATE_AGENT_REVIEW_FORM,
                'value' => NotificationTriggerValue::ON_CREATE_AGENT_REVIEW_FORM,
            ], [
                'name' => NotificationTriggerName::ON_MORE_INFO_PROPERTIES,
                'value' => NotificationTriggerValue::ON_MORE_INFO_PROPERTIES,
            ], [
                'name' => NotificationTriggerName::ON_MORE_INFO_PROPERTIES_FE_USER,
                'value' => NotificationTriggerValue::ON_MORE_INFO_PROPERTIES_FE_USER,
            ], [
                'name' => NotificationTriggerName::ON_SHARE_SAVED_PROPERTIES,
                'value' => NotificationTriggerValue::ON_SHARE_SAVED_PROPERTIES,
            ],
            [
                'name' => NotificationTriggerName::ON_PROPERTY_CHANGE_ALERT,
                'value' => NotificationTriggerValue::ON_PROPERTY_CHANGE_ALERT,
            ],
            [
                'name' => NotificationTriggerName::ON_USER_DEACTIVATION,
                'value' => NotificationTriggerValue::ON_USER_DEACTIVATION,
            ]
        ];

        foreach ($data as $item) {
            $table = new NotificationTrigger;
            $row = $table->where('name', $item['name'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("Notification Trigger - %s has been added \n", $item['name']);
            } else {
                $row->update($item);
            }
        }
    }
}
