<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FormFieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(FormTypesSeeder::class);
        $this->call(CareerFormSeeder::class);
        $this->call(PropertyEnquiryFormSeeder::class);
        $this->call(RegisterPropertyFormSeeder::class);
        $this->call(ContactUsFormSeeder::class);
        $this->call(AgentReviewSeeder::class);
    }
}
