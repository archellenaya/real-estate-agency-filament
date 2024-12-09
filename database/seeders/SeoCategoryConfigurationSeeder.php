<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Constants\Components\SEOPropertyFields;
use App\Models\SeoCategoryConfiguration;

class SeoCategoryConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [ 
            SEOPropertyFields::BEDROOM,
            SEOPropertyFields::BATHROOM,
            SEOPropertyFields::LOCALITY,
            SEOPropertyFields::MARKET_STATUS,
            SEOPropertyFields::MARKET_TYPE,
            SEOPropertyFields::STATUS,
            SEOPropertyFields::PROPERTY_TYPE,
            SEOPropertyFields::PROPERTY_AGREEMENT,
            SEOPropertyFields::PRICE,
            SEOPropertyFields::AREA,
            SEOPropertyFields::REGION,
        ];

        //sequence_no depends on the arrangement of array variable $default_active_fields
        $default_active_fields = [
            SEOPropertyFields::MARKET_TYPE, 
            SEOPropertyFields::BEDROOM, 
            SEOPropertyFields::BATHROOM,  
            SEOPropertyFields::PROPERTY_TYPE, 
            SEOPropertyFields::LOCALITY,
            SEOPropertyFields::REGION,
        ];

        $counter_fields = [SEOPropertyFields::BEDROOM, SEOPropertyFields::BATHROOM];

        foreach($fields as $field) { 
            SeoCategoryConfiguration::updateOrCreate([
                'property_field' => $field,
            ],[ 
                'active' => in_array($field, $default_active_fields),
                'is_count' => in_array($field, $counter_fields),
                'sequence_no' => in_array($field, $default_active_fields) ? (array_search($field, $default_active_fields) + 1):null
            ]);
        }
    } 
}
