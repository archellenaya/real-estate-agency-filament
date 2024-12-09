<?php

namespace Database\Seeders;

use App\Feature;
use Illuminate\Database\Seeder;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FeaturePropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        $properties = Property::all();
        $features = Feature::all();

        $featureCount = $features->count();

        $totalRows = 0;

        foreach ($properties as $property) {
         
            $numberOfFeatures = rand(2, 6);

   
            $randomFeatures = $features->random(min($numberOfFeatures, $featureCount))->pluck('id');

            $totalRows += $randomFeatures->count();

            // Attach the selected features to the property with feature_value "yes"
            foreach ($randomFeatures as $featureId) {
                DB::table('feature_property')->insert([
                    'property_id' => $property->id,
                    'feature_id' => $featureId,
                    'feature_value' => 'Yes',
                ]);
            }
        }
    }
}
