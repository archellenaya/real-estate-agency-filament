<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;
use App\Models\Field;
use Carbon\Carbon;
use App\Constants\Components\FormNames;
use App\Constants\Components\FormValues;


class PropertyEnquiryFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form_key = FormValues::PROPERTY_ENQUIRY;
        $form_type = FormType::where('key', $form_key)->first();

        if(!isset($form_type) && !$form_type){
            $form_type = FormType::create(
                [
                    'name'      => FormNames::PROPERTY_ENQUIRY,
                    'key'       => FormValues::PROPERTY_ENQUIRY,
                    'slug'      => FormValues::PROPERTY_ENQUIRY,
                    'active'    => 1,
                ]
            );
        }

        if( isset($form_type) && $form_type) {

            echo 'Adding form type: '.   $form_type->name;

            $field_group = 'applicant_info';
            $field_group_name = 'Applicant Information';
            
            $fields = [
                [
                    'field_name'        => 'Property',
                    'field_key'         => 'property_id',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Fullname',
                    'field_key'         => 'fullname',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Email',
                    'field_key'         => 'email',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Prefix',
                    'field_key'         => 'prefix_phone_number',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Phone',
                    'field_key'         => 'phone_number',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Comment',
                    'field_key'         => 'comment',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'I want to see this property',
                    'field_key'         => 'want_to_see_property',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Date',
                    'field_key'         => 'date',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Time',
                    'field_key'         => 'time',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],
            ];

            foreach($fields as $field) {
                Field::updateOrCreate($field, [
                    'main_field'       => 1,
                    'updated_at'       => Carbon::now()
                ]);
            }

        } else {
           echo 'FAILED to add: '.FormNames::PROPERTY_ENQUIRY;
        }

    }
}
