<?php

namespace Database\Seeders;

use App\Models\PropertyTypeGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypeGroups = [
            'Residential',
            'Commercial',
            'Industrial',
            'Agricultural'
        ];

        foreach ($propertyTypeGroups as $propertyTypeGroup) {
            PropertyTypeGroup::create(['description' => $propertyTypeGroup]);
        }
    }
}
