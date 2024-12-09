<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IPropertySyncService;
use App\Components\Services\Impl\SyncUtilityService;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Components\Passive\Utilities;
use App\Components\Repositories\IPropertyRepository;
use Illuminate\Support\Facades\Artisan;
use App\Constants\Components\Regions;
use App\Components\Services\ICloudflareService;
use App\Constants\Components\FileStatus;
use App\Models\Region;
use Dflydev\DotAccessData\Util;
use Illuminate\Support\Facades\File;

class PropertySyncService extends SyncUtilityService implements IPropertySyncService
{
    private $_propertyRepository;
    private $_cloudflareService;
    private $_default_options;

    public function __construct(IPropertyRepository $propertyRepository, ICloudflareService $cfService)
    {
        parent::__construct();
        $this->_propertyRepository  = $propertyRepository;
        $this->_cloudflareService   = $cfService;
        $defaults = explode(',', config('app.default_options'));
        $this->_default_options = array_map(function ($item) {
            return strtolower(trim($item));
        }, $defaults);
    }

    public function bulk($raw_datas, $webhook)
    {
        $update_counts = 0;
        $consultants = DB::table("consultants")->get();
        if ($consultants->count() == 0)
            return $update_counts;

        foreach ($raw_datas as $raw_data) {

            $update_counts += $this->process($raw_data, $webhook);
        }

        return $update_counts;
    }

    public function process($raw_data, $webhook = null)
    {
        $transform_prop_data = $this->transform($raw_data);

        try {
            $referenceNumber = $raw_data->referenceNumber;
            $property = $this->_propertyRepository->getPropertyByRef($referenceNumber);


            if (isset($property) && $property) {
                if ($transform_prop_data['consultant_id'] == null) {
                    unset($transform_prop_data['consultant_id']);
                }
                Log::debug("update: " . $referenceNumber);
                $transform_prop_data['old_price_field'] = $property->price_field;
                unset($transform_prop_data['id']);
                unset($transform_prop_data['old_id']);
                $transform_prop_data['date_price_reduced_field'] = Carbon::now(); //not actually a price REDUCED but a PRICE CHANGED
                $this->_propertyRepository->updatePropertyByRef($referenceNumber, $transform_prop_data);
            } else {
                Log::debug("add: " . $referenceNumber);
                $this->_propertyRepository->createProperty($transform_prop_data);
                $property = $this->_propertyRepository->getPropertyByRef($referenceNumber);

                $this->_propertyRepository->updateProperty($property->id, [
                    "slug" => $this->generate_property_inner_slug($property)
                ]);
            }

            // if(strcasecmp(config("app.env"), "production")==0) {
            //     $this->_cloudflareService->purgePropertyByReference($property->property_ref_field); 
            // } 
            $this->feature_property_upsert($property->id, $raw_data);
            $this->setPropertyImageFiles($property->id, $raw_data);
            // Utilities::message(print_r( tenant('id'), true));
            // Artisan::call('cache:prime', [
            //     "ref" => $property->property_ref_field,
            //     "--tenants" => [tenant('id')]
            // ]);
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            DB::table('data_imports')->where('id', $webhook->id)->update(['exception' => $e->getMessage(), 'status' => 'failed']);
            return 0;
        }

        return 1;
    }

    public function setPropertyImageFiles($property_id, $data)
    {
        $current_files = $this->getCurrentFiles($property_id);
        $main_images_path = isset($data->thumbnail->image) ? $data->thumbnail->image->url : null;
        $other_images =  $data->images ?? null;

        $files = [];
        $count = 1;

        if (isset($main_images_path)) {
            $main_image = $this->getPreparedDBImageStatement($main_images_path, $property_id, "MainImage", $count);

            if (!empty($main_image)) {
                $files[] = $main_image;
            }
        }

        if (isset($other_images)) {
            if (is_array($other_images)) {
                foreach ($other_images as $image_path) {
                    $image_url = $image_path->image->url ?? null;
                    if (!empty($image_url)) {
                        $image = $this->getPreparedDBImageStatement($image_url, $property_id, "OtherImages", $count);
                        if (!empty($image)) {
                            $files[] = $image;
                        }
                    }
                }
            }
        }

        $new_files = [];
        if (count($files) > 0) {
            Utilities::message("from REAP:" . count($files));
            foreach ($files as $file) {
                if (isset($current_files[$file['sequence_no_field']])) { // check if not new
                    Utilities::message("sequence_no_field:" . $file['sequence_no_field'] . " exist");
                    if ($current_files[$file['sequence_no_field']] != $file['orig_image_src']) {
                        Utilities::message("but not equal src");
                        $new_files[] = $file;
                    }
                } else {
                    Utilities::message("sequence_no_field:" . $file['sequence_no_field'] . " new");
                    $new_files[] = $file;
                }
            }
            if (count($new_files) > 0) {
                $this->clearPropertyImageFiles($property_id, $new_files);
                return DB::table('files')->insert($new_files);
            }

            Utilities::deleteFilesExceedsSequence($property_id, $file['sequence_no_field']);
            Utilities::message("files are already up to date");
        } else {
            $this->clearPropertyImageFiles($property_id, $new_files); // 2nd args with empty array will delete all existing property files
            Utilities::message("No files in REAP");
        }
    }


    protected function getCurrentFiles($property_id)
    {
        $files = DB::table('files')->select('sequence_no_field', 'orig_image_src')->where('property_id', $property_id)->orderBy('sequence_no_field', 'asc')->get();
        $current_files = [];
        foreach ($files as $file) {
            $current_files[$file->sequence_no_field] = $file->orig_image_src;
        }

        return $current_files;
    }


    protected function getPreparedDBImageStatement($image_path, $property_id, $file_type, &$count)
    {
        try {
            $image_path = $this->stripUrlQueryString($image_path); //remove query param ?x=y

            $filename = basename($image_path);
            $orig =  $filename;

            $file_info = pathinfo($filename);
            $ext = $file_info['extension'];

            $filename = $count . "_" . $property_id . "." . $ext;

            return [
                'property_id' => $property_id,
                'file_name_field' =>  $filename,
                'file_type_field' => $file_type,
                'mime' => $this->mime_content_type($filename),
                'sequence_no_field' => $count++,
                'original_file_name' => $orig,
                'seo_url_field'  => null,
                "orig_image_src" => $image_path,
                "url_field" => $image_path ?? config('url.property_thumbnail') ?: null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'image_status_field' => FileStatus::TO_OPTIMIZE
            ];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
        return [];
    }

    public function getConsultantID($data)
    {
        if (isset($data->name) && isset($data->id)) {
            $consultant = DB::table("consultants")->where('old_id', $data->id)->first();
            if (isset($consultant)) {
                return $consultant->id;
            } else {
                $consultant = $this->defaultConsultant();
                if (isset($consultant)) {
                    return $consultant->id;
                }
                return null;
            }
        } else {
            $consultant = $this->defaultConsultant();

            if (isset($consultant)) {
                return $consultant->id;
            }
            return null;
        }
    }

    private function defaultConsultant()
    {
        $consultant = DB::table("consultants")->where('old_id', config("company.default.consultant.reap_id"))
            ->orWhere("full_name_field", config("company.default.consultant.reap_name"))
            ->first();

        if (isset($consultant)) {
            return $consultant;
        } else {
            return null;
        }
    }

    public function clearPropertyImageFiles($property_id, $files_to_delete)
    {

        if (empty($files_to_delete)) {
            $files = DB::table('files')->where('property_id', $property_id)->get();

            foreach ($files as $file) {
                $file_path = public_path(tenant('id') . "/image/property/" . $file->property_id . "/" . $file->file_name_field);
                try {
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }
                } catch (\Exception $e) {
                    Log::info("Error deleting files:" . json_encode($e));
                    echo ("Error deleting files, check logs\n");
                }
            }
            return  DB::table('files')->where('property_id', $property_id)->delete();
        }

        foreach ($files_to_delete as $file_to_delete) {
            $files = DB::table('files')->where('property_id', $property_id)->where('sequence_no_field', $file_to_delete['sequence_no_field'])->get();
            foreach ($files as $file) {
                Utilities::message("checking:" . $property_id . ": sec: " . $file_to_delete['sequence_no_field']);
                $file_path = public_path(tenant('id') . "/image/property/" . $file->property_id . "/" . $file->file_name_field);
                try {
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }

                    $deleted = DB::table('files')->where('property_id', $property_id)->where('sequence_no_field', $file_to_delete['sequence_no_field'])->delete();

                    if ($deleted) {
                        Utilities::message($property_id . ":deleted sequence_no_field:" . $file->sequence_no_field);
                    }
                } catch (\Exception $e) {
                    Log::info("Error deleting files:" . json_encode($e));
                    echo ("Error deleting files, check logs\n");
                }
            }
        }
    }

    public function formatFeatureName($name)
    {
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    // public function feature_property_upsert($property_id, $data) 
    // {
    //     $this->clear_properties_features($property_id);

    //     $boolean_valued_features = [
    //         'study',
    //         'lift',
    //         'flatlet',
    //         'airspace',
    //         'country-views',
    //         'sea-views',
    //         'freehold',
    //         'advertising',
    //         'keys',
    //         'commercial-potential',
    //         'development-potential',
    //         'basement',
    //         '5-star',
    //         'sole-agency',
    //         'bargain',
    //     ];

    //     $new_property_features = [];
    //     foreach($data->roomsAndFeatures as $feature) { 
    //         $key = Utilities::slugify($feature->title); 
    //         $value = $feature->value ?? "";
    //         $feature_value = in_array($key, $boolean_valued_features) || empty($value) ? 1:$value;

    //         $feature_id = $this->getID('features', [ 'feature_value'=> $this->formatFeatureName($key)], []); 
    //         $new_property_features[] = [
    //             'feature_id'   => $feature_id, 
    //             'property_id'  => $property_id, 
    //             'feature_value' =>  $feature_value
    //         ]; 
    //     } 

    //     return DB::table('feature_property')->insert($new_property_features);
    // }

    public function feature_property_upsert($property_id, $data)
    {
        $this->clear_properties_features($property_id);

        $new_property_features = [];
        foreach ($data->roomsAndFeatures as $feature) {
            $feature_value = (!empty($feature->title) && (empty($feature->value) || is_null($feature->value))) ? 'Yes' : $feature->value;
            $feature_id = $this->getID('features', ['feature_value' => $feature->title], []);

            $new_property_features[] = [
                'feature_id'   => $feature_id,
                'property_id'  => $property_id,
                'feature_value' =>  $feature_value
            ];
        }

        return DB::table('feature_property')->insert($new_property_features);
    }

    public function clear_properties_features($property_id)
    {
        return DB::table('feature_property')->where('property_id', $property_id)->delete();
    }

    public function getLocalityID($data)
    {
        if (isset($data->title) && isset($data->id)) {
            $existingRegion = DB::table('regions')->where('old_id', $data->region->id)->first();
            if (!$existingRegion) {
                $new_region = [
                    'old_id' => $data->region->id,
                    'description' => $data->region->title,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $existingRegion['id'] = DB::table("regions")->insertGetId($new_region);
            }

            $existingCountry = DB::table('countries')->where('code', $data->country->code)->first();
            if (!$existingCountry) {
                $new_country = [
                    'old_id' => $data->country->id,
                    'description' => $data->country->title,
                    'code' => $data->country->code,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $existingCountry['id'] = DB::table("countries")->insertGetId($new_country);
            }

            $locality = DB::table("locality")->where('locality_name', trim($data->title))->first();

            if (isset($locality)) {
                return $locality->id;
            } else {
                try {
                    $new_locality =  [
                        'old_id' => $data->id,
                        'locality_name' => trim($data->title),
                        'description' => $data->description ?? null,
                        'region' => $data->region->title ?? null,
                        'post_code' => $data->post_code ?? null,
                        'status' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'country_id' => $existingCountry->id,
                        'region_id' => $existingRegion->id
                    ];

                    DB::table("locality")->insert($new_locality);

                    return $this->getLocalityID($data);
                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                }
            }
        } else {
            return null;
        }
    }

    public function getProjectID($data)
    {
        if (isset($data->name) && isset($data->id)) {
            $project = DB::table("projects")->where('old_id', $data->id)->first();
            if (isset($project)) {
                return $project->id;
            } else {
                try {
                    $photo_url = $data->photo;

                    if (isset($photo_url)) {
                        $orig_filename = basename($photo_url);
                        $file_info = pathinfo($orig_filename);
                        $filename = $file_info['filename'];
                        $ext = $file_info['extension'];
                        $timestamp  = time();
                        $newfilename =  $filename . '_' . $timestamp  . "." . $ext;
                        $path = 'projects\\' . $newfilename;
                        $encoded_url = $this->encoded($photo_url);
                        Storage::put($path, $this->image_get_contents($encoded_url));
                    }

                    $new_project =  [
                        'old_id' => $data->id,
                        'name' => $data->name,
                        'summary' => $data->body_summary ?? null,
                        'filename' => $newfilename ?? null,
                        'description' => $data->body ?? null,
                        'original_photo_url' => $photo_url ?? null,
                        'status' => $data->status ?? 0,
                        'updated_at' => Carbon::now(),
                        'created_at' => Carbon::now(),
                    ];

                    DB::table("projects")->insert($new_project);

                    return $this->getProjectID($data);
                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                }
            }
        } else {
            return null;
        }
    }

    public function transform($data)
    {
        $json_data = json_encode($data);
        $meta_data = null;
        $region = isset($data->locality->region) ? $data->locality->region->title : null;
        $isFeatured = 0;
        $tags = [];
        foreach ($data->tags as $tag) {
            if (strtolower($tag->type) ==  "featured") {
                $isFeatured = 1;
            }
            $tags[trim($tag->type)] = trim($tag->name);
        }
        if (!empty($tags)) {
            $meta_data['tags'] = $tags;
        }
        $availabilityDate = null;
        $sql_max_date = 2037;
        try {
            $availabilityDateYear   = isset($data->availabilityDate) ? Carbon::parse($data->availabilityDate)->format("Y") : null;
            $availabilityDate       = isset($data->availabilityDate) ? Carbon::parse($data->availabilityDate)->format("Y-m-d H:i:s") : null;

            if ($availabilityDateYear > $sql_max_date) {
                $availabilityDateMonth  = isset($data->availabilityDate) ? Carbon::parse($data->availabilityDate)->format("m") : null;
                $availabilityDateDay    = isset($data->availabilityDate) ? Carbon::parse($data->availabilityDate)->format("d") : null;
                $availabilityDate       = new Carbon("$sql_max_date-$availabilityDateMonth-$availabilityDateDay");
            }
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }


        return [

            'is_featured_field'             => $isFeatured,
            'old_id'                        => (string)($data->id ?? null),
            'consultant_id'                 => $this->getConsultantID($data->agent), //isset( $agent_name ) ? $this->getID('consultants', ['full_name_field' => $agent_name], ['id' => $this->generate_consultant_id($agent_name)]) : null, 
            'locality_id_field'             => isset($data->locality) ? $this->getLocalityID($data->locality) : null, //('locality', ['locality_name' => $this->extractValue($data->field_locality)], []), 
            'id'                            => (string)($data->referenceNumber),
            'property_ref_field'            => (string)($data->referenceNumber),
            'area_field'                    => $data->totalArea ?? null, //[ 'numeric' ],
            'price_field'                   => (float) $data->price ?? null,
            'old_price_field'               => (float) $data->price ?? null,
            'date_available_field'          => $availabilityDate, //$data->availabilityDate ?? null,//[ 'date' ], 
            'bedrooms_field'                => $data->numberOfBedrooms ?? null, //[ 'integer' ],
            'bathrooms_field'               => $data->numberOfBathrooms ?? null, //[ 'integer' ],
            'long_description_field'        => $data->writeUp ?? null, // [ 'required' ],
            'market_type_field'             => $data->isSale == true ? "Sale" : "Rent", //to check
            'sole_agents_field'             => $data->isSoleAgency, //[ 'required', 'boolean' ],
            'is_managed_property'           => $data->isManagedProperty, //[ 'boolean' ],
            'por_field'                     => $data->priceOnRequest,
            'property_type_id_field'        => isset($data->category->code) ? $this->getID('propertytype', ['code' => $data->category->code], ['description' => $data->category->title]) : null,
            'status'                        => 1, //ok
            'region_field'                  => Utilities::getRegionID($region) ?? null,
            'market_status_field'           => $data->currentStatus ?? null, //ok 
            'property_status_id_field'      => (isset($data->finishedType->title) && $data->finishedType->title != "Default") ? $this->getID('property_status', ['description' => $data->finishedType->title], []) : null,
            'orig_created_at'               => isset($data->created) ? Carbon::parse($data->created)->format("Y-m-d H:i:s") : null,
            'updated_at'                    => isset($data->lastUpdated) ? Carbon::parse($data->lastUpdated)->format("Y-m-d H:i:s") : null, //$data->lastUpdated ?? null,
            // 'expiry_date_time'            => isset($data->expiresOn) ?  Carbon::parse($data->expiresOn)->format("Y-m-d H:i:s"):null, //$data->expiresOn ?? null,//not sure
            'virtual_tour_url_field'        => $data->virtualTourLink ?? null, //[ 'url', 'max:200' ],
            'title_field'                   => $data->propertyTitle ?? null, //[ 'required', 'string', 'max:200' ],
            'specifications_field'          => isset($data->specifications) ? json_encode($data->specifications) : null, //[], 
            'items_included_in_price_field' => $data->includedInPrice ?? null, //[],
            'premium_field'                 => $data->premiumPrice ?? null, //[],
            'rent_period_field'             => isset($data->rentalPriceType->title) ? $data->rentalPriceType->title : null, //[ 'string', 'in:Daily,Weekly,Monthly,Annual' ],
            'weight_field'                  => $data->weight ?? null, //[ 'numeric' ],
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
            // 'project_id'                => $this->getProjectID($data->field_project), 
            'commercial_field'              => $data->isResidential == true ? false : true,
            // 'latitude_field'             => $this->extractValue($data->field_latitude) ?? null,//[ 'numeric' ],
            // 'longitude_field'            => $this->extractValue($data->field_longitude) ?? null,//[ 'numeric' ],
            // 'is_property_of_the_month_field' => $this->extractValue($data->field_property_of_the_month) ?? null,//[ 'required', 'boolean' ],field_featured
            'external_area_field'           => $data->externalArea ?? null,
            'internal_area_field'           => $data->internalArea ?? null,
            'plot_area_field'               => $data->plotArea ?? null,
            'data'                          => $json_data,
            'meta_data'                     => $meta_data,
            'to_synch'                      => 0
        ];
    }

    public function getAllProperties()
    {
        return $this->_propertyRepository->getAllProperties();
    }

    public function generate_property_inner_slug($property)
    {
        //[market-type]-[property-type]-in-[locality]-[region]-[ref] 
        //for-sale-apartment-in-swieqi-malta-30107
        $slug = [];

        if (!empty($property->market_type_field)) {
            $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field;
            $slug[] = sprintf('for-%s', Utilities::slugify($market_type));
        }

        if (!empty($property->property_type->description) && !in_array(strtolower($property->property_type->description), $this->_default_options)) {
            $slug[] = Utilities::slugify(sprintf('%s', $property->property_type->description));
        }

        if (!empty($property->locality->locality_name) && !in_array(strtolower($property->locality->locality_name), $this->_default_options)) {
            $slug[] = sprintf('in-%s', Utilities::slugify($property->locality->locality_name));
        }

        $region = Utilities::getRegionByID($property->region_field);

        if ((!empty($property->region_field) && !empty($region->description)) && !in_array(strtolower($region->description), $this->_default_options)) {
            $slug[] = Utilities::slugify(sprintf('%s', $region->description));
        }

        $slug[] = $property->property_ref_field;

        return implode("-", $slug);
    }
}
