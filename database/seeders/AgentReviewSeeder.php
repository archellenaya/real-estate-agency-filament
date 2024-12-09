<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;
use App\Models\Field;
use Carbon\Carbon;
use App\Constants\Components\FormNames;
use App\Constants\Components\FormValues;


class AgentReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form_key   = FormValues::ADD_AGENT_REVIEW;
        $form_type  = FormType::where('key', $form_key)->first();

        if(!isset($form_type) && !$form_type){
            $form_type = FormType::create(
                [
                    'name'      => FormNames::ADD_AGENT_REVIEW,
                    'key'       => FormValues::ADD_AGENT_REVIEW,
                    'slug'      => FormValues::ADD_AGENT_REVIEW,
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
                    'field_name'        => 'Country',
                    'field_key'         => 'country',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ],[
                    'field_name'        => 'Review',
                    'field_key'         => 'review_comment',
                    'field_group'       => $field_group,
                    'field_group_name'  => $field_group_name,
                    'form_type_id'      => $form_type->id,
                ]
            ];

            foreach($fields as $field) {
                Field::updateOrCreate($field, [
                    'main_field'  => 1,
                    'updated_at'  => Carbon::now()
                ]);
            }

        } else {
           echo 'FAILED to add: '.FormNames::ADD_AGENT_REVIEW;
        }

    }
}
