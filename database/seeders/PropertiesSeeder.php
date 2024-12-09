<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataImport; 
use App\Constants\Components\DataImportStatus;
use Carbon\Carbon; 
use App\Components\Passive\Utilities;
use Illuminate\Support\Facades\Log;


class PropertiesSeeder extends Seeder
{

 
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 
        try { 
            $limit = 5;
            for($i=1; $i<=$limit; $i++) {
                $payload = $this->generateData();  
                DataImport::create([ 
                    'name' => config('cms.name'),
                    'status' => DataImportStatus::NEW,
                    'payload' => $payload, 
                ]);  
            }
        } catch (\Exception $e ) {
            Log::debug($e->getMessage()); 
        }

    }
    public function generateData() {
        $data = [
            
        ];

        $payload = ["reap-properties" => $data];
                    
        return $payload;   
    }
  

    public function generateData2() 
    {
        return [
            
            'is_featured_field'           => $isFeatured, 
            'old_id'                    => (string)($data->id ?? null),  
            'consultant_id'             => $this->getConsultantID($data->agent), //isset( $agent_name ) ? $this->getID('consultants', ['full_name_field' => $agent_name], ['id' => $this->generate_consultant_id($agent_name)]) : null, 
            'locality_id_field'           => $this->getLocalityID($data->locality), //('locality', ['locality_name' => $this->extractValue($data->field_locality)], []), 
            'id'                        => (string)($data->referenceNumber),
            'property_ref_field'          => (string)($data->referenceNumber),
            'area_field'                 => $data->totalArea ?? null,//[ 'numeric' ],
            'price_field'                => (double) $data->price ?? null,
            'old_price_field'             => (double) $data->price ?? null,
            'date_available_field'        => $availabilityDate,//$data->availabilityDate ?? null,//[ 'date' ], 
            'bedrooms_field'             => $data->numberOfBedrooms ?? null,//[ 'integer' ],
            'bathrooms_field'            => $data->numberOfBathrooms ?? null,//[ 'integer' ],
            'long_description_field'      => $data->writeUp ?? null,// [ 'required' ],
            'market_type_field'           => $data->isSale==true ? "Sale":"Rent", //to check
            'sole_agents_field'           => $data->isSoleAgency,//[ 'required', 'boolean' ],
            'is_managed_property'         => $data->is_managed_property,//[ 'boolean' ],
            'por_field'                  => $data->priceOnRequest,
            'property_type_id_field'       => isset($data->category->title) ? $this->getID('propertytype', ['description' => $data->category->title], []) ?? 2:null,
            'status'                    => 1,//ok
            'region_field'               => Utilities::getRegionID($region) ?? null,
            'market_status_field'         => $data->currentStatus ?? null,//ok 
            'property_status_id_field'     => (isset($data->finishedType->title) && $data->finishedType->title!="Default") ? $this->getID('property_status', ['description' => $data->finishedType->title], []): null, 
            'orig_created_at'           => isset($data->created) ? Carbon::parse($data->created)->format("Y-m-d H:i:s"):null,
            'updated_at'                => isset($data->lastUpdated) ? Carbon::parse($data->lastUpdated)->format("Y-m-d H:i:s"):null,//$data->lastUpdated ?? null,
            // 'expiry_date_time'            => isset($data->expiresOn) ?  Carbon::parse($data->expiresOn)->format("Y-m-d H:i:s"):null, //$data->expiresOn ?? null,//not sure
            'virtual_tour_url_field'       => $data->virtualTourLink ?? null,//[ 'url', 'max:200' ],
            'title_field'                => $data->propertyTitle ?? null,//[ 'required', 'string', 'max:200' ],
            'specifications_field'       => $data->specifications ?? null,//[], 
            'items_included_in_price_field' => $data->includedInPrice ?? null,//[],
            'premium_field'              => $data->premiumPrice ?? null,//[],
            'rent_period_field'           => isset($data->rentalPriceType->title) ? $data->rentalPriceType->title:null, //[ 'string', 'in:Daily,Weekly,Monthly,Annual' ],
            'weight_field'               => $data->weight ?? null,//[ 'numeric' ],
            'property_block_id_field'      => null,//[ 'integer' ],
            'contact_details_field'       => null,//[ 'string', 'max:100' ],  
            'description_field'          => "",//[ 'required', 'string' ],
            'is_hot_property_field'        => null,//[ 'required', 'boolean' ],
            'date_on_market_field'         => null,//[ 'required', 'date' ],
            'date_off_market_field'        => null,//[ 'date' ],
            'date_price_reduced_field'     => null,//[ 'date' ], 
            'three_d_walk_through'         => null,//[ 'url', 'max:200' ],
            'show_on_3rd_party_sites_field'  => null,//[ 'required', 'boolean' ],
            'prices_starting_from_field'   => null,//[ 'required', 'boolean' ],
            'hot_property_title_field'     => null,//[ 'string', 'max:200' ],
            // 'project_id'                => $this->getProjectID($data->field_project), 
            // 'commercial_field'           => $this->extractValue($data->field_commercial_potential), 
            // 'latitude_field'             => $this->extractValue($data->field_latitude) ?? null,//[ 'numeric' ],
            // 'longitude_field'            => $this->extractValue($data->field_longitude) ?? null,//[ 'numeric' ],
            // 'is_property_of_the_month_field' => $this->extractValue($data->field_property_of_the_month) ?? null,//[ 'required', 'boolean' ],field_featured
        ];
    }
}
