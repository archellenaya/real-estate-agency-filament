<?php

namespace App\Components\Services\Impl;

use Exception;
use Carbon\Carbon;
use App\Models\Region;
use App\Models\Locality;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Components\Passive\Utilities;
use App\Constants\Components\FileStatus;
use App\Components\Services\ICloudflareService;
use App\Components\Services\IPropertySyncService;
use App\Components\Repositories\IPropertyRepository;
use App\Components\Services\Impl\SyncUtilityService;
use App\Models\PropertyType;

abstract class NotionPropertySyncBaseService extends SyncUtilityService implements IPropertySyncService
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

        $raw_datas = Utilities::recursiveTrim($raw_datas);

        $update_counts = 0;

        // Fetch consultants once and handle the case when no consultants are available
        $consultants = DB::table("consultants")->get();
        if ($consultants->isEmpty())
            return $update_counts;

        // Process each raw data item
        foreach ($raw_datas as $raw_data) {
            try {
                $update_counts += $this->process($raw_data, $webhook);
            } catch (Exception $e) {
                Log::debug($e->getMessage());
                return 0;
            }
        }

        return $update_counts;
    }

    public function process($raw_data, $webhook = null)
    {
        $raw_data = Utilities::recursiveTrim($raw_data);

        $transformed_prop_data = $this->transform($raw_data);


        try {
            $referenceNumber = $this->extractReferenceNumber($raw_data);
            $property = $this->_propertyRepository->getPropertyByRef($referenceNumber);

            if ($property) {
                $this->updateExistingProperty($property, $transformed_prop_data);
            } else {
                $property = $this->createNewProperty($transformed_prop_data, $referenceNumber);
            }

            $this->postProcessing($property, $raw_data);

            return 1;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            DB::table('data_imports')->where('id', $webhook->id)->update(['exception' => $e->getMessage(), 'status' => 'failed']);
            return 0;
        }
    }
    abstract public function transform($data);

    public function getAllProperties()
    {
        return $this->_propertyRepository->getAllProperties();
    }

    public function extractReferenceNumber($raw_data)
    {
        $keys = ["LL#", "Commercial Address", "S#"];

        foreach ($keys as $key) {
            if (isset($raw_data[$key]["title"][0]["plain_text"])) {
                return str_replace("#", '', $raw_data[$key]["title"][0]["plain_text"]);
            }
        }

        return null;
    }

    private function updateExistingProperty(&$property, &$transform_prop_data)
    {
        if ($transform_prop_data['consultant_id'] == null) {
            unset($transform_prop_data['consultant_id']);
        }

        Log::debug("Update: " . $property->property_ref_field);
        $transform_prop_data['old_price_field'] = $property->price_field;
        unset($transform_prop_data['id'], $transform_prop_data['old_id']);
        $transform_prop_data['date_price_reduced_field'] = Carbon::now(); // not actually a price REDUCED but a PRICE CHANGED

        $this->_propertyRepository->updatePropertyByRef($property->property_ref_field, $transform_prop_data);
    }

    private function createNewProperty(&$transform_prop_data, $referenceNumber)
    {
        Log::debug("Add: " . $referenceNumber);
        $this->_propertyRepository->createProperty($transform_prop_data);
        $property = $this->_propertyRepository->getPropertyByRef($referenceNumber);

        return $property;
    }

    private function postProcessing($property, $raw_data)
    {

        $this->_propertyRepository->updateProperty($property->property_ref_field, [
            "slug" => $this->generate_property_inner_slug($property)
        ]);
        // if (strcasecmp(config("app.env"), "production") == 0) {
        //     $this->_cloudflareService->purgePropertyByReference($property->property_ref_field);
        // }

        $this->feature_property_upsert($property->id, $raw_data);
        $this->setPropertyMediaFiles($property->id, $raw_data);
        // Utilities::message(print_r(tenant('id'), true));
        // Artisan::call('cache:prime', [
        //     "ref" => $property->property_ref_field,
        //     "--tenants" => [tenant('id')]
        // ]);
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

    private function feature_property_upsert($property_id, $data)
    {
        $this->clear_properties_features($property_id);

        $new_property_features = $this->extractNewPropertyFeatures($property_id, $data);

        return DB::table('feature_property')->insert($new_property_features);
    }

    public function setPropertyMediaFiles($property_id, $data)
    {
        $current_files = $this->getCurrentFiles($property_id);

        $main_media_path = $data["Pictures and video"]["files"][0]['file']['url'] ?? $data["Website Pictures and video"]["files"][0]['file']['url'] ?? null;
        $other_media = $data["Pictures and video"]["files"] ??  $data["Website Pictures and video"]["files"] ?? [];

        $files = [];
        $count = 1;

        if (isset($main_media_path)) {
            $main_media = $this->getPreparedDBMediaStatement($main_media_path, $property_id, "MainImage", $count);

            if (!empty($main_media)) {
                $files[] = $main_media;
            }
        }

        if (isset($other_media)) {
            if (is_array($other_media)) {
                foreach ($other_media as $media) {
                    $media_url = $media["file"]["url"] ?? null;
                    if (!empty($media_url)) {
                        $media = $this->getPreparedDBMediaStatement($media_url, $property_id, "OtherImages", $count);

                        if (!empty($media)) {
                            $files[] = $media;
                        }
                    }
                }
            }
        }

        $new_files = [];
        if (count($files) > 0) {
            Utilities::message("from NOTION:" . count($files));
            foreach ($files as $file) {
                if (isset($current_files[$file['sequence_no_field']])) {
                    Utilities::message("sequence_no_field:" . $file['sequence_no_field'] . " exist");
                    if ($current_files[$file['sequence_no_field']]['original_file_name'] != $file['original_file_name'] || $current_files[$file['sequence_no_field']]['image_status_field'] === FileStatus::FAILED) {

                        Utilities::message("but not equal original file name and is failed");
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
            Utilities::message("No files in NOTION");
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

    public function clear_properties_features($property_id)
    {
        return DB::table('feature_property')->where('property_id', $property_id)->delete();
    }

    private function extractNewPropertyFeatures($property_id, $data)
    {
        $new_property_features = [];

        $feature_names = array_column($data["Features"]["multi_select"], 'name');

        $existing_features = DB::table('features')
            ->whereIn('feature_value', $feature_names)
            ->pluck('id', 'feature_value')
            ->toArray();

        foreach ($feature_names as $feature_name) {
            $feature_id = $existing_features[$feature_name] ?? null;

            if (!$feature_id) {
                $feature_id = DB::table('features')->insertGetId([
                    'feature_value' => $feature_name,
                    'created_at' =>  Carbon::now(),
                    'updated_at' =>  Carbon::now()
                ]);
            }

            $new_property_features[] = [
                'feature_id'   => $feature_id,
                'property_id'  => $property_id,
                'feature_value' => "Yes"
            ];
        }

        return $new_property_features;
    }

    protected function getPreparedDBMediaStatement($media_path, $property_id, $file_type, &$count)
    {
        try {
            $stripped_image_path = $this->stripUrlQueryString($media_path);
            $filename = basename($stripped_image_path);
            $file_mime = $this->mime_content_type($filename);

            $orig =  $filename;

            $file_info = pathinfo($filename);
            $ext = $file_info['extension'];

            $filename = $count . "_" . $property_id . "." . $ext;
            $image_status = FileStatus::TO_OPTIMIZE;
            $url_field = $media_path;

            if (strpos($file_mime, 'video/') === 0) {
                if ($file_type === "MainImage") {
                    return [
                        'property_id' => $property_id,
                        'file_name_field' => 'default.webp',
                        'file_type_field' => $file_type,
                        'mime' => 'image/webp',
                        'sequence_no_field' => $count++,
                        'original_file_name' => 'default.webp',
                        'seo_url_field'  => null,
                        "orig_image_src" => config('url.property_thumbnail') ?? null,
                        "url_field" => config('url.property_thumbnail') ?? null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'image_status_field' => FileStatus::READY
                    ];
                } elseif ($file_type === "OtherImages") {
                    $file_type = "Video";
                    $url_field =  url('/' . tenant('id') . "/api/v2/property/video/$filename", [], true);
                    $image_status = FileStatus::READY;
                }
            }

            return [
                'property_id' => $property_id,
                'file_name_field' =>  $filename,
                'file_type_field' => $file_type,
                'mime' => $this->mime_content_type($filename),
                'sequence_no_field' => $count++,
                'original_file_name' => $orig,
                'seo_url_field'  => null,
                "orig_image_src" => $media_path,
                "url_field" => $url_field ?? config('url.property_thumbnail') ?: null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'image_status_field' => $image_status
            ];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
        return [];
    }

    protected function getCurrentFiles($property_id)
    {
        $files = DB::table('files')
            ->select('sequence_no_field', 'original_file_name', 'image_status_field')
            ->where('property_id', $property_id)
            ->orderBy('sequence_no_field', 'asc')
            ->get();

        $current_files = [];

        foreach ($files as $file) {
            $current_files[$file->sequence_no_field] = [
                'original_file_name' => $file->original_file_name,
                'image_status_field' => $file->image_status_field
            ];
        }

        return $current_files;
    }

    public function getLocalityID($old_id)
    {
        if (isset($old_id)) {
            $locality = Locality::where('old_id', $old_id)->first();
            $region = Region::where('description', $locality?->region)->first();
            if (isset($locality)) {
                return [
                    'locality_id' => $locality->id,
                    'region_id' => $region->id ?? null
                ];
            } else {
                return null;
            }
        } else {
            return null;
        }
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

    public function extractPropertyDescription($property_description_data)
    {
        $property_description = "";
        foreach ($property_description_data as $description_part) {
            $property_description .= $description_part["plain_text"] . " " ?? '';
        }
        return Utilities::removeEmojis($property_description) ?? null;
    }

    protected function getOrCreateLocalityAndRegion($data)
    {
        $localityData = $data["Locality"]["multi_select"][0] ?? null;
        $regionData = $data["Region"]["select"] ?? null;

        if (empty($localityData) || empty($localityData['name'])) {
            return ['locality_id' => null, 'region_id' => null];
        }

        $regionName = $regionData['name'] ?? null;

        $locality = Locality::where('old_id', $localityData['id'] ?? null)->first();

        if ($locality) {

            $locality->update([
                'locality_name' => ucwords($localityData['name']),
                'region'        => $regionName ? ucwords($regionName) : null,
                'status'        => 1
            ]);
            $localityID = $locality->id;
        } else {

            $localityID = Locality::firstOrCreate(
                ['locality_name' => ucwords($localityData['name'])],
                [
                    'old_id'        => $localityData['id'] ?? null,
                    'locality_name' => ucwords($localityData['name']),
                    'region'        => $regionName ? ucwords($regionName) : null,
                    'status'        => 1
                ]
            )->id;
        }


        if ($regionName) {
            $region = Region::where('old_id', $regionData['id'] ?? null)->first();

            if ($region) {

                $region->update([
                    'description' => ucwords($regionName)
                ]);
                $regionID = $region->id;
            } else {

                $regionID = Region::firstOrCreate(
                    ['description' => $regionName],
                    [
                        'old_id'      => $regionData['id'] ?? null,
                        'description' => ucwords($regionName)
                    ]
                )->id;
            }
        } else {
            $regionID = null;
        }

        return [
            'locality_id' => $localityID,
            'region_id'   => $regionID
        ];
    }

    protected function getOrCreatePropertyType($propertyTypeName, $oldID)
    {
        return PropertyType::firstOrCreate(
            ['description' => $propertyTypeName],
            ['old_id' => $oldID, 'property_type_groupId' => 1]
        );
    }
}
