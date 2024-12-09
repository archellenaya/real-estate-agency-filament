<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;
use App\Models\Field;
use Carbon\Carbon;
use App\Constants\Components\FormNames;
use App\Constants\Components\FormValues;

class ContactUsFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form_key = FormValues::CONTACT_US;
        $form_type = FormType::where('key', $form_key)->first();

        if(!isset($form_type) && !$form_type){
            $form_type = FormType::create(
                [
                    'name'      => FormNames::CONTACT_US,
                    'key'       => FormValues::CONTACT_US,
                    'slug'      => FormValues::CONTACT_US,
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
                    'field_name'        => 'Country',
                    'field_key'         => 'country',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Message',
                    'field_key'         => 'message',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ]
            ];

            foreach($fields as $field) {
                Field::updateOrCreate($field, [
                    'main_field'       => 1,
                    'updated_at'       => Carbon::now()
                ]);
            }

        } else {
           echo 'FAILED to add: '.FormNames::CONTACT_US;
        }

    }
}
