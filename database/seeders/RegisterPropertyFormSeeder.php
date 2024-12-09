<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;
use App\Models\Field;
use Carbon\Carbon;
use App\Constants\Components\FormNames;
use App\Constants\Components\FormValues;


class RegisterPropertyFormSeeder extends Seeder
{
   /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form_key = FormValues::REGISTER_PROPERTY;
        $form_type = FormType::where('key', $form_key)->first();

        if(!isset($form_type) && !$form_type){
            $form_type = FormType::create(
                [
                    'name'      => FormNames::REGISTER_PROPERTY,
                    'key'       => FormValues::REGISTER_PROPERTY,
                    'slug'      => FormValues::REGISTER_PROPERTY,
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
                    'field_name'        => 'Name',
                    'field_key'         => 'name',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ], [
                    'field_name'        => 'Surname',
                    'field_key'         => 'surname',
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
                    'field_name'        => 'Property Location',
                    'field_key'         => 'location',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Residential',
                    'field_key'         => 'residential',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Bedrooms',
                    'field_key'         => 'number_of_bedroom',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Bathrooms',
                    'field_key'         => 'number_of_bathroom',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Are you looking to sell, let or both',
                    'field_key'         => 'purpose',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Property Description',
                    'field_key'         => 'property_description',
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
           echo 'FAILED to add: '.FormNames::REGISTER_PROPERTY;
        }
    }
}
