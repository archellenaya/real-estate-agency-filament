<?php

namespace Database\Seeders;

use App\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {


        $propertyFeatures = [
            'Swimming Pool',
            'Gym',
            'Parking',
            'Garden',
            'Security System',
            'Balcony',
            'Fireplace',
            'Central Heating',
            'Air Conditioning',
            'Home Theater',
            'Wine Cellar',
            'Sauna',
            'Solar Panels',
            'Jacuzzi',
            'Smart Home System',
            'Rooftop Terrace',
            'Guest House',
            'Outdoor Kitchen',
            'Tennis Court',
            'Basketball Court',
            'Playground',
            'Pet-Friendly',
            'Waterfront',
            'Mountain View',
            'City View',
            'Walk-in Closet',
            'High Ceilings',
            'Hardwood Floors',
            'Stainless Steel Appliances',
            'Granite Countertops'
        ];

        foreach ($propertyFeatures as $feature) {
            Feature::create([
                'feature_value' => $feature
            ]);
        }
    }
}
