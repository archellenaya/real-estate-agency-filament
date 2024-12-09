<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyType;
use App\Models\PropertyTypeGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PropertyTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            'Maisonette',
            'Apartment',
            'Villa',
            'Penthouse',
            'Studio',
            'Loft',
            'Duplex',
            'Cottage',
            'Bungalow',
            'Townhouse'
        ];

        foreach ($propertyTypes as $type) {
            $propertyTypeGroup = PropertyTypeGroup::inRandomOrder()->first();
            PropertyType::create([
                'description' => $type,
                'property_type_groupId' => $propertyTypeGroup->id
            ]);
        }
    }
}
