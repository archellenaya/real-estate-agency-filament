<?php
namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IFormRepository;
use App\Constants\Components\NotificationTriggerValue;
use App\Constants\Components\ApplicationStatus;
use Illuminate\Support\Facades\DB;
use App\Models\ApplicationAttachment;
use App\Models\Application;
use App\Models\FormType;
use App\Models\Field;
use App\Models\ApplicationMeta;
use Illuminate\Support\Facades\Storage;
use App\Components\Passive\Encryption;
use App\Constants\Components\AttachmentTypes;
use App\Constants\Components\FormValues;
use App\Components\Services\IEmailNotificationService;
use App\Constants\Components\NotificationTriggerName;
use App\Models\DTO\PropertyEnqueryDTO;

class FormRepository implements IFormRepository
{
    private $_emailNotificationService;

    public function __construct(IEmailNotificationService $emailNotificationService)
    {
        $this->_emailNotificationService = $emailNotificationService;
    }


    public function processForm($interpolation_properties, $form_type, $recipients)
    {
        $result = DB::transaction(function () use ($interpolation_properties, $form_type, $recipients) {

            $formType = FormType::where('key', $form_type)->first();
            if( empty($formType) === true ) {
                return null;
            }
            $application = Application::create([
                'form_type_id' => $formType->id,
                'status' => ApplicationStatus::PENDING,
            ]);
            $attachments = array();

            foreach ($interpolation_properties as $field => $value) {
                if (is_string($value) && is_file($value)) {
                    $filepath = 'public/attachments/applications/' . $form_type . '/' . $application->id . '/';
                    $file = $value;
                    // Filename is hashed filename + part of timestamp
                    $newFilename  = md5($file->getClientOriginalName() . uniqid());
                    $fileContents = Encryption::encrypt(file_get_contents($file->getRealPath()));
                    $fileLocation = $filepath . $newFilename;
                    Storage::disk('local')->put($fileLocation, $fileContents);
                    $filename = $file->getClientOriginalName();
                    $content_type = $file->getClientMimeType();

                    $attachment = ApplicationAttachment::create([
                        'application_id'   => $application->id,
                        'attachment_name'  => isset($filename) ? $filename : null,
                        'attachment_file'  => $fileLocation,
                        'attachment_mime'  => $content_type,
                        'attachment_type'  => $this->attachmentType($form_type, $field)
                    ]);

                    array_push(
                        $attachments,
                        [
                            'attachment_name' => $attachment->attachment_name,
                            'attachment_file' => $attachment->attachment_file,
                            'attachment_mime' => $attachment->attachment_mime
                        ]
                    );
                } else {
                    $field = Field::where('field_key', $field)->where('form_type_id', $formType->id)->first();
                    // firstOrCreate( WHY NOT FIRST OR CREATE APPROACH
                    //     [
                    //       'field_key' => $field,
                    //       'form_type_id'=> $formType->id
                    //     ]
                    // );
                    if (isset($field) && $field) {
                        ApplicationMeta::create([
                            'application_id' => $application->id,
                            'field_id'       => $field->id,
                            'meta_value'     => $value,
                        ]);
                    }
                }
            }

            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                $this->getEmailNotificationTrigger($form_type),
                $recipients,
                $attachments
            );

        });

        return $result;
    }

    private function attachmentType($formType, $field)
    {
        if (strcmp(FormValues::CAREER, strtolower($formType)) == 0 && strcmp('cv_upload', strtolower($field)) == 0) {
            return AttachmentTypes::CAREER_CV_UPLOAD;
        }
        if (strcmp(FormValues::CAREER, strtolower($formType)) == 0 && strcmp('cover_letter_upload', strtolower($field)) == 0) {
            return AttachmentTypes::CAREER_COVER_LETTER_UPLOAD;
        }
    }

    public function getEmailNotificationTrigger($form_type)
    {
        switch ($form_type) {
            case FormValues::PROPERTY_ENQUIRY:
                return NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM;
            case FormValues::PROPERTY_ENQUIRY_FE_USER:
                return NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM_FE_USER;
            case FormValues::CAREER:
                return NotificationTriggerValue::ON_CREATE_CAREER_FORM;
            case FormValues::REGISTER_PROPERTY:
                return NotificationTriggerValue::ON_CREATE_PROPERTY_REGISTRATION_FORM;
            case FormValues::CONTACT_US:
                return NotificationTriggerValue::ON_CREATE_CONTACT_US_FORM;
            case FormValues::ADD_AGENT_REVIEW:
                return NotificationTriggerValue::ON_CREATE_AGENT_REVIEW_FORM;
        }
    }

    public function getLists($form_type)
    {
        $formType = FormType::where('key', $form_type)->first();

        $applications = Application::where("form_type_id",  $formType->id)->get();

        $lists = array();

        foreach ($applications as $application) {
            $data = $this->getApplicationData($application);
            $lists[] = $data->application->applicant_info->info ?? '';
        }

        return $lists;
    }

    private function getApplicationData($application)
    {
        $data = new \stdClass();

        if (!$application) {
            return response()->json('Application Does Not Exists', 404);
        }
        $metas = $application->metas()->with('field')->orderBy('field_id', 'ASC')->get();
        $metas = $metas->groupBy('field.field_group');
        $meta_groups = [];
        foreach ($metas as $key => $meta) {
            $meta_groups[$key] = $meta->groupBy('field.sub_field_group');
        }
        $data_groups = new \stdClass();
        foreach ($meta_groups as $key => $group) {
            if ($key) {
                $groups = new \stdClass();
                foreach ($group as $key2 => $value) {
                    if ($key2 != '') {
                        $sub_groups = array();
                        $value = $value->groupBy('meta_group_id');

                        foreach ($value as $sub_group) {
                            $groupValue = array();
                            $groupValue = $sub_group->pluck('meta_value', 'field.field_name');
                            $groupValue['meta_group_id'] = $sub_group->first()['meta_group_id'];
                            $sub_groups[] = $groupValue;
                        }
                        $groups->{$key2} = $sub_groups;
                    } else {
                        $groupValue = $value->pluck('meta_value', 'field.field_name');
                        $groups->{'info'} = $groupValue;
                    }
                }
                $data_groups->{$key} = $groups;
                $application->{$key} = $groups;
            }
        }
        $data->application = $application;
        $data->meta_groups = $meta_groups;
        $data->data_groups = $data_groups;
        return $data;
    }
}
