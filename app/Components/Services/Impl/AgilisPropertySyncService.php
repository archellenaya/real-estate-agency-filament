<?php

namespace App\Components\Services\Impl;

use Exception;
use Carbon\Carbon;
use App\Models\Region;
use App\Models\Locality;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;
use App\Constants\Components\Regions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Constants\Components\FileStatus;
use App\Components\Services\ICloudflareService;
use App\Components\Services\IPropertySyncService;
use App\Components\Services\Impl\SyncUtilityService;
use App\Components\Repositories\IPropertyRepository;
use App\Models\PropertyType;
use Illuminate\Support\Facades\File;

class AgilisPropertySyncService extends SyncUtilityService implements IPropertySyncService
{
    private $_propertyRepository;
    private $_cloudflareService;

    public function __construct(IPropertyRepository $propertyRepository, ICloudflareService $cfService)
    {
        parent::__construct();
        $this->_propertyRepository  = $propertyRepository;
        $this->_cloudflareService   = $cfService;
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

        $orig_id = $raw_data['id'];
        try {
            
            $property = $this->_propertyRepository->getPropertyOldId($orig_id);

            if (isset($property) && $property) {
                if ($transform_prop_data['consultant_id'] == null) {
                    unset($transform_prop_data['consultant_id']);
                }
                Log::debug("update: " . $orig_id);
                $transform_prop_data['old_price_field'] = $property->price_field;
                unset($transform_prop_data['id']);
                unset($transform_prop_data['old_id']);
                $transform_prop_data['date_price_reduced_field'] = Carbon::now(); //not actually a price REDUCED but a PRICE CHANGED
                $this->_propertyRepository->updatePropertyByOldId($orig_id, $transform_prop_data);
                $property = $this->_propertyRepository->getPropertyOldId($orig_id);
            } else {
                Log::debug("add: " . $orig_id);
                $property = $this->_propertyRepository->createProperty($transform_prop_data);
            }

            $this->_propertyRepository->updatePropertyByOldId($orig_id, [
                "slug" => $this->generate_property_inner_slug($property)
            ]);

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
        if (!isset($data["images"])) {
            $this->clearPropertyImageFiles($property_id, $data["images"]);
            return;
        }
        $main_image_path = isset($data['images'][0]['fileUrl']) ? $data['images'][0]['fileUrl'] : null;
        $current_files = $this->getCurrentFiles($property_id);


        $count = 1;
        $files = [];
        if (isset($main_image_path)) {
            $main_image = $this->getPreparedDBImageStatement($main_image_path, $property_id, "MainImage", $count);

            if (!empty($main_image)) {
                $files[] = $main_image;
            }
        }

        foreach ($data["images"] as $image) {
            $image_url = $image["fileUrl"] ?? null;
            $image = null;
            if (!empty($image_url)) {
                $image = $this->getPreparedDBImageStatement($image_url, $property_id, "OtherImages", $count);
            }

            if (!empty($image)) {
                $files[] = $image;
            }
        }

        $new_files = [];
        if (count($files) > 0) {
            Utilities::message("from AGILIS:" . count($files));
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
            Utilities::message("No files in AGILIS");
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
            // $image_path = $this->stripUrlQueryString($image_path);//remove query param ?x=y
            $image_path = str_replace("/staging", "", $image_path);
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
                'image_status_field' => FileStatus::TO_OPTIMIZE,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
        return [];
    }

    public function getConsultantID($old_id)
    {
        if (isset($old_id)) {
            $consultant = DB::table("consultants")->where('old_id', $old_id)->first();
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

    public function feature_property_upsert($property_id, $data)
    {
        $this->clear_properties_features($property_id);

        $new_property_features = [];
        foreach ($data["propertyFeatures"] as $feature) {
            $value = $feature["value"] ?? "";
            $feature_id = $this->getID('features', ['feature_value' => $feature["description"]], []);
            $new_property_features[] = [
                'feature_id'   => $feature_id,
                'property_id'  => $property_id,
                'feature_value' => $value
            ];
        }

        return DB::table('feature_property')->insert($new_property_features);
    }

    public function clear_properties_features($property_id)
    {
        return DB::table('feature_property')->where('property_id', $property_id)->delete();
    }

    public function getLocalityID($old_id)
    {
        if (isset($old_id)) {
            $locality = Locality::where('old_id', $old_id)->first();
            if (isset($locality)) {
                return [
                    'locality_id' => $locality->id,
                    'region_id' => $locality->zone->region_model->id ?? null
                ];
            } else {
                return null;
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

        $price = $data["isToLet"] == true ? ((float) $data["leasePrice"] ?? null) : ($data["salePrice"] ?? null);
        $bedrooms =  $data["bedrooms"] ?? 0;
        $bathrooms = 0;
        foreach ($data["propertyFeatures"] as $feature) {
            if ($feature["id"] == "28382" || (!empty($feature["description"]) && str_contains(strtolower($feature["description"]), 'bathroom'))) {
                $bathrooms = $feature["value"] ?? 0;
                break;
            }
        }

        $locality = $this->getLocalityID($data["localityId"] ?? null);

        if (isset($data["marketStatus"]) && $data["marketStatus"]) {
            $market_status = preg_replace('/\s+/', '', $data["marketStatus"]);
        }

        $status = 0;
        if (isset($data['images']) && !empty($data['images'])) {
            if ($market_status == 'OnMarket' || $data['advertiseOnWebsite'] == true) {
                $status = 1;
            }
        }

        return [
            // 'is_featured_field'             => $isFeatured, 
            'old_id'                        => (string)($data["id"] ?? null),
            'consultant_id'                 => $this->getConsultantID($data["websiteContactId"] ?? null),
            'locality_id_field'             => $locality["locality_id"] ?? null,
            'id'                            => (string)($data["propertyRef"]),
            'property_ref_field'            => (string)($data["propertyRef"]),
            'area_field'                    => $data["totalPropertyArea"] ?? null, //[ 'numeric' ],
            'price_field'                   => $price,
            'old_price_field'               => $price,
            'date_available_field'          => isset($data["lettingNextAvailableDate"]) ? Carbon::parse($data["lettingNextAvailableDate"])->format("Y-m-d H:i:s") : null, //[ 'date' ], 
            'bedrooms_field'                => $bedrooms, //[ 'integer' ],
            'bathrooms_field'               => $bathrooms ?? 0, //[ 'integer' ],
            'long_description_field'        => $data["propertyDescription"] ?? null, // [ 'required' ],
            'market_type_field'             => $data["isForSale"] == true ? "Sale" : "Rent", //to check
            // 'sole_agents_field'             => $data->isSoleAgency,//[ 'required', 'boolean' ],
            // 'is_managed_property'           => $data->isManagedProperty,//[ 'boolean' ],
            'por_field'                     => $data["priceOnRequest"] ?? null,
            'property_type_id_field'        => isset($data["propertySubTypeId"]) ? $this->getID('propertytype', ['old_id' => $data["propertySubTypeId"]], []) : null,
            'status'                        => $status,
            'region_field'                  => $locality["region_id"] ?? null,
            'market_status_field'           => $market_status ?? null, //ok 
            'property_status_id_field'      => isset($data['propertyStateName']) ? $this->getID('property_status', ['description' => $data['propertyStateName']], ['description' => $data['propertyStateName']]) : null,
            'orig_created_at'               => isset($data["dateRegistered"]) ? Carbon::parse($data["dateRegistered"])->format("Y-m-d H:i:s") : null,
            'updated_at'                    => isset($data["lastUpdateDate"]) ? Carbon::parse($data["lastUpdateDate"])->format("Y-m-d H:i:s") : null, //$data->lastUpdated ?? null,
            // 'expiry_date_time'            => isset($data->expiresOn) ?  Carbon::parse($data->expiresOn)->format("Y-m-d H:i:s"):null, //$data->expiresOn ?? null,//not sure
            'virtual_tour_url_field'        => $data->virtualTourLink ?? null, //[ 'url', 'max:200' ],
            'title_field'                   => $data->propertyTitle ?? null, //[ 'required', 'string', 'max:200' ],
            'specifications_field'          => $data["specifications"] ?? null, //[], 
            'items_included_in_price_field' => $data["itemsInclInPrice"] ?? null, //[],
            'premium_field'                 => $data->premiumPrice ?? null, //[],
            'rent_period_field'             => $data["leaseRatePeriod"] ?? null, //[ 'string', 'in:Daily,Weekly,Monthly,Annual' ],
            'weight_field'                  => $data->weight ?? null, //[ 'numeric' ],
            'property_block_id_field'       => null, //[ 'integer' ],
            'contact_details_field'         => null, //[ 'string', 'max:100' ],  
            'description_field'             => $data["shortDescription"] ?? null, //[ 'required', 'string' ],
            'is_hot_property_field'         => $data["hotProperty"] ?? null, //[ 'required', 'boolean' ],
            'date_on_market_field'          => null, //[ 'required', 'date' ],
            'date_off_market_field'         => isset($data["dateOffMarket"]) ? Carbon::parse($data["dateOffMarket"])->format("Y-m-d H:i:s") : null, //[ 'date' ],
            'date_price_reduced_field'      => null, //[ 'date' ], 
            'three_d_walk_through'          => null, //[ 'url', 'max:200' ],
            'show_on_3rd_party_sites_field' => null, //[ 'required', 'boolean' ],
            'prices_starting_from_field'    => null, //[ 'required', 'boolean' ],
            'hot_property_title_field'      => null, //[ 'string', 'max:200' ],
            // 'project_id'                => $this->getProjectID($data->field_project), 
            'commercial_field'              => $data["isResidential"] == true ? false : true,
            'latitude_field'                => $data["googleMapLatitude"] ?? null,
            'longitude_field'               => $data["googleMapLongitude"] ?? null,
            // 'is_property_of_the_month_field' => $this->extractValue($data->field_property_of_the_month) ?? null,//[ 'required', 'boolean' ],field_featured
            'external_area_field'           => $data->externalArea ?? null,
            'internal_area_field'           => $data->internalArea ?? null,
            'plot_area_field'               => $data->plotArea ?? null,
            'data'                          => $json_data,
            'to_synch'                      => 0
        ];
    }

    public function getAllProperties()
    {
        return $this->_propertyRepository->getAllProperties();
    }

    public function generate_property_inner_slug($property)
    {
        $slug = [];

        if (!empty($property->market_type_field)) {
            $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field;
            $slug[] = sprintf('for-%s', Utilities::slugify($market_type));
        }

        if (!empty($property->property_type->description)) {
            $slug[] = Utilities::slugify(sprintf('%s', $property->property_type->description));
        }

        if (!empty($property->locality->locality_name)) {
            $slug[] = sprintf('in-%s', Utilities::slugify($property->locality->locality_name));
        }

        $region = Region::where('id', $property->region_field)->first();

        if (!empty($property->region_field) && !empty($region)) {
            $slug[] = Utilities::slugify(sprintf('%s', $region->description));
        }

        $slug[] = $property->property_ref_field;

        return implode("-", $slug);
    }
}
