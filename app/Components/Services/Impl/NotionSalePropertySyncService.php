<?php

namespace App\Components\Services\Impl;

use Carbon\Carbon;
use App\Models\Region;
use App\Models\Locality;

class NotionSalePropertySyncService extends NotionPropertySyncBaseService
{
    public function bulk($raw_datas, $webhook)
    {
        return parent::bulk($raw_datas, $webhook);
    }

    public function process($raw_data, $webhook = null)
    {

        return parent::process($raw_data, $webhook);
    }
    public function transform($data)
    {

        $json_data = json_encode($data);

        $property_ref = $this->extractReferenceNumber($data);

        $final_price = isset($data["List Price"]["number"]) ? (float)$data["List Price"]["number"] : null;
        $date_available = $data["Available on"]["date"]["start"] ?? null;
        $date_created = $data["Date Created"]["created_time"] ?? null;

        $propertyType = $data["Property Type"]["select"] ?? null;
        if ($propertyType && isset($propertyType["name"], $propertyType["id"])) {
            $propertyType = $this->getOrCreatePropertyType($propertyType["name"], $propertyType["id"]);
        }
        $ids = $this->getOrCreateLocalityAndRegion($data);

        return [

            'old_id'                        => (string)($data["S#"]["title"][0]["plain_text"] ?? null),
            'consultant_id'                 => $this->getConsultantID($data["Assigned Agent"]["people"][0]["id"] ?? null),
            'locality_id_field'             => $ids['locality_id'] ?? null,
            'id'                            => $property_ref,
            'property_ref_field'            => $property_ref,
            'area_field'                    => $data['Total sqm']['number'] ?? null, //[ 'numeric' ],
            'price_field'                   => $final_price,
            'old_price_field'               => $final_price,
            'date_available_field'          => isset($date_available) && $date_available !== null ? Carbon::parse($date_available)->format("Y-m-d H:i:s") : null, //$data->availabilityDate ?? null,//[ 'date' ], 
            'bedrooms_field'                => intval(isset($data["Bedroom"]["select"]["name"]) ? $data["Bedroom"]["select"]["name"] : null), //[ 'integer' ],
            'bathrooms_field'               => intval(isset($data["Bathroom"]["multi_select"][0]["name"]) ? $data["Bathroom"]["multi_select"][0]["name"] : null), //[ 'integer' ],
            'long_description_field'        => $this->extractPropertyDescription($data["Property Description"]["rich_text"] ?? []) ?? null, // [ 'required' ],
            'market_type_field'             => 'Sale' ?? null,
            'por_field'                     => 0,
            'property_type_id_field'        => $propertyType->id ?? null,
            'status'                        => 0,
            'region_field'                  => $ids['region_id'] ?? null,
            'market_status_field'           => null,
            'property_status_id_field'      => null,
            'orig_created_at'               => isset($date_created) ? Carbon::parse($date_created)->format("Y-m-d H:i:s") : null,
            'updated_at'                    => null, //$data->lastUpdated ?? null,
            'virtual_tour_url_field'        => null, //[ 'url', 'max:200' ],
            'title_field'                   => null, //[ 'required', 'string', 'max:200' ],
            'specifications_field'          => null,
            'items_included_in_price_field' => null,
            'premium_field'                 => null,
            'rent_period_field'             => $rentPeriod ?? null, //[ 'string', 'in:Daily,Weekly,Monthly,Annual' ],
            'weight_field'                  => null, //[ 'numeric' ],
            'property_block_id_field'       => null, //[ 'integer' ],
            'contact_details_field'         => null, //[ 'string', 'max:100' ],  
            'description_field'             => "", //[ 'required', 'string' ],
            'is_hot_property_field'         => null, //[ 'required', 'boolean' ],
            'date_on_market_field'          => null, //[ 'required', 'date' ],
            'date_off_market_field'         => null, //[ 'date' ],
            'date_price_reduced_field'      => null, //[ 'date' ], 
            'three_d_walk_through'          => null, //[ 'url', 'max:200' ],
            'show_on_3rd_party_sites_field' => null, //[ 'required', 'boolean' ],
            'prices_starting_from_field'    => null, //[ 'required', 'boolean' ],
            'hot_property_title_field'      => null, //[ 'string', 'max:200' ],
            'commercial_field'              => false,
            'latitude_field'                => null,
            'longitude_field'               => null,
            'external_area_field'           => intval(isset($data["Sqm Ext"]["rich_text"][0]["plain_text"]) ? $data["Sqm Ext"]["rich_text"][0]["plain_text"] : null),
            'internal_area_field'           => intval(isset($data["Sqm int"]["rich_text"][0]["plain_text"]) ? $data["Sqm int"]["rich_text"][0]["plain_text"] : null),
            'plot_area_field'               => null,
            'data'                          => $json_data,
            'to_synch'                      => 0
        ];
    }
}
