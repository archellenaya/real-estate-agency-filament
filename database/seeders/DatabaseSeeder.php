<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call(NotificationTriggerSeeder::class);
        // $this->call(EmailTemplateSeeder::class);
        // $this->call(UniqueLinkTypeSeeder::class);
        // $this->call(BuyerTypeSeeder::class);
        // $this->call(InterestSeeder::class);
        // $this->call(TypeSeeder::class);
        // $this->call(EmailFrequencySeeder::class);
        // $this->call(UserTypesSeeder::class);
        $this->call(UserTestSeeder::class);
        // $this->call(FormFieldsTableSeeder::class);
        // $this->call(SeoCategoryConfigurationSeeder::class);
    }
}
