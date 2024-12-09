<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;
use Carbon\Carbon;
use App\Constants\Components\FormNames;
use App\Constants\Components\FormValues;

class FormTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $forms = [
            [
                'name'      => FormNames::CONTACT_US,
                'key'       => FormValues::CONTACT_US,
                'slug'      => FormValues::CONTACT_US,
                'active'    => 1,
            ],[
                'name'      => FormNames::CAREER,
                'key'       => FormValues::CAREER,
                'slug'      => FormValues::CAREER,
                'active'    => 1,
            ],[
                'name'      => FormNames::PROPERTY_ENQUIRY,
                'key'       => FormValues::PROPERTY_ENQUIRY,
                'slug'      => FormValues::PROPERTY_ENQUIRY,
                'active'    => 1,
            ],[
                'name'      => FormNames::REGISTER_PROPERTY,
                'key'       => FormValues::REGISTER_PROPERTY,
                'slug'      => FormValues::REGISTER_PROPERTY,
                'active'    => 1,
            ],[
                'name'      => FormNames::ADD_AGENT_REVIEW,
                'key'       => FormValues::ADD_AGENT_REVIEW,
                'slug'      => FormValues::ADD_AGENT_REVIEW,
                'active'    => 1,
            ],
            [
                'name'      => FormNames::PROPERTY_ENQUIRY_FE_USER,
                'key'       => FormValues::PROPERTY_ENQUIRY_FE_USER,
                'slug'      => FormValues::PROPERTY_ENQUIRY_FE_USER,
                'active'    => 1,
            ]
        ];

        foreach($forms as $form) {
            FormType::updateOrCreate($form, [
                'updated_at'       => Carbon::now()
            ]);
        }
    }
}
