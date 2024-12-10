<?php

namespace App\Components\Services\Impl;

use App\Components\Services\ICloudflareService;
use App\Models\Consultant;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CloudflareService implements ICloudflareService
{

    private $_base_url, $_identifier, $_api_key, $_api_base_url, $_purge_limit;

    public function __construct()
    {
        $this->_base_url     = config('app.cloudflare.url');
        $this->_identifier   = config('app.cloudflare.zone_identifier');
        $this->_api_key      = config('app.cloudflare.api_key');
        $this->_api_base_url = config("app.url");
        $this->_purge_limit  = 30;
    }

    public function purgeUrls($urls)
    {
        if (!empty($urls)) {
            $guzzle = new \GuzzleHttp\Client(['base_uri' =>  $this->_base_url]);
            $endpoint = sprintf("/client/v4/zones/%s/purge_cache", $this->_identifier);
            try {
                $raw_response = $guzzle->post($endpoint, [
                    'headers' => ['Authorization' => 'Bearer ' . $this->_api_key],
                    'body' => json_encode([
                        "files" => $urls
                    ]),
                ]);
                Log::debug("Purge successfully.");
                return  $raw_response->getBody()->getContents();
            } catch (Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            Log::debug("No files to purge.");
            return "No files to purge.";
        }
    }

    public function getPropertyImageUrls($property_id)
    {
        $base_url = $this->_api_base_url;

        $files = DB::table('files')->where('property_id', $property_id)->get();
        $urls = [];
        foreach ($files as $file) {
            if ($file->file_type_field == "MainImage") {
                $urls[] =  $file->url_field . "?width=380";
            } else {
                $urls[] =  $file->url_field;
                $urls[] =  $file->url_field . "?width=1800";
                $urls[] =  $file->url_field . "?width=229";
            }
        }
        return $urls;
    }

    public function getConsultantImage($consultant_id)
    {
        if (empty($consultant_id)) {
            return [];
        }
        $consultant = DB::table("consultants")->where('id', $consultant_id)->first();
        return [
            $consultant->url_field . "?width=151"
        ];
    }

    public function purgePropertyByReference($reference)
    {
        $property = DB::table('properties')->where('property_ref_field', $reference)->first();
        if (isset($property) && $property) {
            $base_url = $this->_api_base_url;
            $urls = [
                $base_url . "/api/v2/properties/byRefs?refs=" . $reference,
            ];

            $consultant_urls = $this->getConsultantImage($property->consultant_id);
            $property_urls   = $this->getPropertyImageUrls($property->id);
            $merge_urls      = array_merge($urls, $property_urls, $consultant_urls);
            $results = [];
            if (!empty($merge_urls))
                foreach (array_chunk($merge_urls, $this->_purge_limit) as $item) {
                    $results[] = $this->purgeUrls($item);
                }
            Log::debug(print_r($results, true));
            return $results;
        } else {
            Log::debug("property not found");
            return "property not found";
        }
    }
}
