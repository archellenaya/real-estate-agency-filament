<?php

namespace Database\Seeders;

use App\Models\PropertyStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyStatuses = [
            'Available',
            'Coming Available',
        ];

        foreach ($propertyStatuses as $propertyStatus) {
            PropertyStatus::create(['description' => $propertyStatus]);
        }
    }
}
