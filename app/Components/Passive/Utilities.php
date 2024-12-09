<?php

namespace App\Components\Passive;

use Carbon\Carbon;
use App\Models\Region;
use App\Components\Passive\Slack;
use App\Constants\Http\StatusCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use Illuminate\Support\Facades\File;
use App\Constants\Components\Regions;
use Illuminate\Support\Facades\Cache;
use App\Constants\Components\RegionValues;
use App\Constants\Components\DataProviders;
use App\Constants\Exception\ProcessExceptionMessage;

class Utilities
{
    public static function data_providers($providers = [])
    {
        if (empty($providers)) {
            return [
                DataProviders::REAP,
                DataProviders::AGILIS,
                DataProviders::NOTION,
            ];
        } else {
            return $providers;
        }
    }

    public static function notifySlack($message, $status_code = 500)
    {
        $notify_who = "";
        if (in_array(strtolower(config('app.env')), ['production'])) {
            $notify_who = config("cms.notify_admin_on_slack_when_failed");
        }

        Slack::reportError(new ProcessException(
            "*" . tenant('id') . ":* " . $message . " " . $notify_who,
            $status_code
        ));
    }

    public static function stripUrlQueryString($url)
    {
        $url_array = parse_url($url);
        Log::debug($url_array);
        $url = $url_array['scheme'] . '://' . $url_array['host'] . $url_array['path'];

        $url = str_replace("/styles//public", "", $url);
        return $url;
    }

    public static function createCacheKey($type, $id, $aid = null)
    {
        $key = "";
        switch ($type) {
            case 'property':
                $cache_key =  tenant('id') . "-property-" . $id;
                $key = $cache_key  . (isset($aid) ? '-aid-' . $aid : '');
                break;

            default:
                Utilities::message("Type unsupported.");
        }
        return $key;
    }


    public static function generateExpiryAt($minutes = null)
    {
        if (empty($minutes)) {
            $minutes = config('token.expiry_in_minutes');
        }

        if (!is_integer($minutes)) {
            throw new ProcessException(
                ProcessExceptionMessage::TOKEN_EXPIRY_MUST_BE_A_NUMBER,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return Carbon::now()->addMinutes($minutes);
    }

    public static function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function image_get_contents($url)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public static function image_get_contents2($url)
    {
        $ch = curl_init();
        $timeout = 0;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        // Getting binary data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $image = curl_exec($ch);
        curl_close($ch);
        return $image;
    }

    public static function encoded($str)
    {

        $pos = strrpos($str, '/') + 1;
        return substr($str, 0, $pos) . rawurlencode(substr($str, $pos));
    }

    public static function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            // 'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'mp4' => 'video/mp4',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(explode('.', $filename)[1]);
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

    public static function clear_cache($type, $key_reference = null)
    {
        switch ($type) {
            case 'property':
                if (empty($key_reference)) {
                    Utilities::message("Unsuccessfull clear caching. key is missing");
                    return;
                }
                $cache_key = config("app.cache_prefix") . "-property-" . $key_reference;
                Cache::forget($cache_key);
                Utilities::message("Successfully forget key: " . $cache_key);
                if (config("cache.default") == "memcached") {
                    Cache::tags([$cache_key])->flush();
                    Utilities::message("Successfully flushed tag: " . $cache_key);
                }
                break;

            case 'all':
                if (config("cache.default") == "memcached") {
                    Cache::tags([Utilities::slugify(config("app.name"))])->flush(); //clear entire custom cached 
                    Utilities::message("Successfully flushed tag: " . Utilities::slugify(config("app.name")));
                } else {
                    Utilities::message("clear all supports memcached only.");
                }
                break;

            default:
                Utilities::message("Type unsupported.");
        }
    }

    public static function message($msg)
    {
        echo "$msg\n";
        Log::info($msg);
    }

    // public static function getRegionID($region)
    // {
    //     if (isset($region)) {

    //         if (strcasecmp("Sliema Area", $region) == 0) {
    //             return RegionValues::SLIEMA_AND_ST_JULIANS;
    //         }

    //         if (strcasecmp("Raguza", $region) == 0 || strcasecmp("Siracusa", $region) == 0) {
    //             return RegionValues::SICILY;
    //         }

    //         $regions = [
    //             RegionValues::NORTH                 => Regions::NORTH,
    //             RegionValues::SOUTH                 => Regions::SOUTH,
    //             RegionValues::CENTRAL               => Regions::CENTRAL,
    //             RegionValues::GOZO                  => Regions::GOZO,
    //             RegionValues::SLIEMA_AND_ST_JULIANS => Regions::SLIEMA_AND_ST_JULIANS,
    //             RegionValues::SICILY                => Regions::SICILY
    //         ];

    //         foreach ($regions as $key => $value) {
    //             if (strcasecmp($value, $region) == 0) {
    //                 return $key;
    //             }
    //         }
    //         return null;
    //     } else {
    //         return null;
    //     }
    // }

    public static function getRegionID($region)
    {
        return Region::where("description", $region)->value('id');
    }


    // public static function getRegion($id)
    // {
    //     if (isset($id)) {
    //         $regions = [
    //             RegionValues::NORTH                 => Regions::NORTH,
    //             RegionValues::SOUTH                 => Regions::SOUTH,
    //             RegionValues::CENTRAL               => Regions::CENTRAL,
    //             RegionValues::GOZO                  => Regions::GOZO,
    //             RegionValues::SLIEMA_AND_ST_JULIANS => Regions::SLIEMA_AND_ST_JULIANS,
    //             RegionValues::SICILY                => Regions::SICILY
    //         ];
    //         return $regions[$id] ?? null;
    //     } else {
    //         return null;
    //     }
    // }

    public static function getRegionByID($id)
    {
        return Region::where("id", $id)->first();
    }

    public static function generate_property_inner_title($property)
    {
        //[market-type]-[property-type]-in-[locality]-[region]-[ref] 
        //for-sale-apartment-in-swieqi-malta-30107
        $title = [];

        if (!empty($property->market_type_field)) {
            $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field;
            $title[] = sprintf('For %s', ucfirst($market_type));
        }

        if (!empty($property->property_type->description)) {
            $title[] = ucfirst(sprintf('%s', $property->property_type->description));
        }

        if (!empty($property->locality->locality_name)) {
            $title[] = sprintf('in %s', ucfirst($property->locality->locality_name));
        }

        $region = Utilities::getRegionByID($property->region_field);

        if (!empty($property->region_field) && !empty($region->description)) {
            $title[] = ucfirst(sprintf('%s', $region->description));
        }

        $title[] = $property->property_ref_field;

        return implode(" ", $title);
    }

    public static function createRequestBody($entity, $page = 1, $limit = 10, $params = null)
    {
        $bodyValue = "";
        $filterVar = '$filters';
        switch ($entity) {
            case 'properties':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    { \\n\\t\\t\\tcurrentStatus,\\n\\t\\t\\tid,\\n\\t\\t\\treferenceNumber,\\n\\t\\t\\tisResidential,\\n\\t\\t\\tslug,\\n\\t\\t\\tavailabilityDate,\\n\\t\\t\\texpiresOn,\\n\\t\\t\\t\\tproject\\n\\t\\t\\t\\t{\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\tsalesDescription\\n\\t\\t\\t\\t},\\n\\t\\t\\t\\n\\t\\t\\ttags {\\n\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\ttype},\\n\\t\\t\\tisNew,\\n\\t\\t\\tisResidential,\\n\\t\\t\\tlocality {\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\tdescription,\\n\\t\\t\\t\\t\\t\\tregion {\\n\\t\\t\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\t\\t\\tid},\\n\\t\\t\\t\\t\\t\\tcountry {\\n\\t\\t\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\t\\t\\tid} }, category {\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\tcode}, \\n\\t\\t\\tpremiumPrice,\\n\\t\\t\\tprice,\\n\\t\\t\\tpriceOnRequest,\\n\\t\\t\\trentalPriceType{\\n\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tblockOfPropertiesIdentifier,\\n\\t\\t\\tisManagedProperty,\\n\\t\\t\\tisSoleAgency,\\n\\t\\t\\tspecifications,\\n\\t\\t\\tincludedInPrice,\\n\\t\\t\\tweight,\\n\\t\\t\\tpropertyTitle,\\n\\t\\t\\tattachments {\\n\\t\\t\\t\\t\\tdisplayName,\\n\\t\\t\\t\\t\\tattachment {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t},\\n\\t\\t\\tyoutubeLink, \\n\\t\\t\\tagent {\\n\\t\\t\\t\\t\\tid, \\tagentCode,\\n      externalIdentifier,\\n\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\tsurname,\\n\\t\\t\\t\\t\\temail,\\n\\t\\t\\t\\t\\tslug,\\n\\t\\t\\t\\t\\tprofileImage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t\\t\\twhatsAppNumber, \\n\\t\\t\\t\\t\\tcontactNumber,\\n\\t\\t\\t\\t\\tcalendlyId, \\n\\t\\t\\t}, \\n \\t\\t \\n\\t\\t\\tnumberOfCarSpaces,\\n\\t\\t\\tpropertyTitle,\\n\\t\\t\\tvirtualTourLink,\\n\\t\\t\\tfurnishedType {\\n\\t\\t\\t\\tid,\\n\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tfinishedType{\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tnumberOfBedrooms,\\n\\t\\t\\tnumberOfBathrooms,\\nexternalArea,\\ninternalArea,\\nplotArea,\\n\\t\\t\\ttotalArea, \\n\\t\\t\\troomsAndFeatures {  \\n\\t\\t\\t\\t\\ttitle, \\n\\t\\t\\t\\t\\tvalue\\n\\t\\t\\t},\\n\\t\\n\\t\\t\\tisSale,\\n\\t\\t\\tcreated,\\n\\t\\t\\tlastUpdated,\\n\\t\\t\\twriteUp, \\n\\t\\t\\tthumbnail\\n\\t\\t\\t{\\n\\t\\t\\t\\t\\timage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t}\\n\\t\\t\\timages {\\n\\t\\t\\t\\t\\timage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t},\\n    }\\n  }\\n}\\n\\n\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $filter_date_modified}}}";
                break;

            case 'properties_modified':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {\\n\\t\\t\\treferenceNumber }\\n  }\\n}\\n\\n\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $filter_date_modified}}}";
                break;

            case 'properties_with_ref':
                $filter_ref = !empty($params) ? ", \"referenceNumbers\":[$params]" : "";
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    { \\n\\t\\t\\tcurrentStatus,\\n\\t\\t\\tid,\\n\\t\\t\\treferenceNumber,\\n\\t\\t\\tisResidential,\\n\\t\\t\\tslug,\\n\\t\\t\\tavailabilityDate,\\n\\t\\t\\texpiresOn,\\n\\t\\t\\t\\tproject\\n\\t\\t\\t\\t{\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\tsalesDescription\\n\\t\\t\\t\\t},\\n\\t\\t\\t\\n\\t\\t\\ttags {\\n\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\ttype},\\n\\t\\t\\tisNew,\\n\\t\\t\\tisResidential,\\n\\t\\t\\tlocality {\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\tdescription,\\n\\t\\t\\t\\t\\t\\tregion {\\n\\t\\t\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\t\\t\\tid}, \\n\\t\\t\\t\\t\\t\\tcountry {\\n\\t\\t\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\t\\t\\tcode,\\n\\t\\t\\t\\t\\t\\t\\tid} }, category {\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\t\\tcode}, \\n\\t\\t\\tpremiumPrice,\\n\\t\\t\\tprice,\\n\\t\\t\\tpriceOnRequest,\\n\\t\\t\\trentalPriceType{\\n\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tblockOfPropertiesIdentifier,\\n\\t\\t\\tisManagedProperty,\\n\\t\\t\\tisSoleAgency,\\n\\t\\t\\tspecifications,\\n\\t\\t\\tincludedInPrice,\\n\\t\\t\\tweight,\\n\\t\\t\\tpropertyTitle,\\n\\t\\t\\tattachments {\\n\\t\\t\\t\\t\\tdisplayName,\\n\\t\\t\\t\\t\\tattachment {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t},\\n\\t\\t\\tyoutubeLink, \\n\\t\\t\\tagent {\\n\\t\\t\\t\\t\\tid, \\tagentCode,\\n      externalIdentifier,\\n\\t\\t\\t\\t\\tname,\\n\\t\\t\\t\\t\\tsurname,\\n\\t\\t\\t\\t\\temail,\\n\\t\\t\\t\\t\\tslug,\\n\\t\\t\\t\\t\\tprofileImage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t\\t\\twhatsAppNumber, \\n\\t\\t\\t\\t\\tcontactNumber,\\n\\t\\t\\t\\t\\tcalendlyId, \\n\\t\\t\\t}, \\n \\t\\t \\n\\t\\t\\tnumberOfCarSpaces,\\n\\t\\t\\tpropertyTitle,\\n\\t\\t\\tvirtualTourLink,\\n\\t\\t\\tfurnishedType {\\n\\t\\t\\t\\tid,\\n\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tfinishedType{\\n\\t\\t\\t\\t\\tid,\\n\\t\\t\\t\\t\\ttitle\\n\\t\\t\\t},\\n\\t\\t\\tnumberOfBedrooms,\\n\\t\\t\\tnumberOfBathrooms,\\nexternalArea,\\ninternalArea,\\nplotArea,\\n\\t\\t\\ttotalArea, \\n\\t\\t\\troomsAndFeatures {  \\n\\t\\t\\t\\t\\ttitle, \\n\\t\\t\\t\\t\\tvalue\\n\\t\\t\\t},\\n\\t\\n\\t\\t\\tisSale,\\n\\t\\t\\tcreated,\\n\\t\\t\\tlastUpdated,\\n\\t\\t\\twriteUp, \\n\\t\\t\\tthumbnail\\n\\t\\t\\t{\\n\\t\\t\\t\\t\\timage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t}\\n\\t\\t\\timages {\\n\\t\\t\\t\\t\\timage {\\n\\t\\t\\t\\t\\t\\t\\turl,\\n\\t\\t\\t\\t\\t},\\n\\t\\t\\t},\\n    }\\n  }\\n}\\n\\n\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $filter_ref}}}";
                break;

            case 'properties_with_ref_filter_and_assigned_agents':
                $filter_ref = !empty($params) ? ", \"referenceNumbers\":[$params]" : "";
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    { \\n\\t\\t\\treferenceNumber, \\n\\t\\t\\tagent { id } }  } } \",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $filter_ref}}}";
                break;

            case 'properties_with_filter_agent_ids':
                $filter_ref = !empty($params) ? ", \"agentIdentifiers\":[$params]" : "";
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n     { referenceNumber  }  } } \",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $filter_ref}}}";
                break;


            case 'get_all_deleted_property_ids':
                $expiresBefore = !empty($params) ? $params : Carbon::now()->toDateString();
                $expiresBefore = sprintf(", \"expiresBefore\":\"%s\"", $expiresBefore);
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {  referenceNumber } } }\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $expiresBefore}}}";
                break;

            case 'get_all_property_ids':
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {  referenceNumber } } }\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit}}}";
                break;

            case 'get_all_property_reap_id':
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {  id } } }\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit}}}";
                break;

            case 'get_all_property_reap_id_sale_type':
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {  referenceNumber } } }\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit, \"isSale\": \"true\"}}}";
                break;

            case 'get_all_property_reap_id_rent_type':
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput)\\n { \\n properties(filters: $filterVar)\\n  {\\n    count\\n    data\\n    {  referenceNumber } } }\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit, \"isSale\": \"false\"}}}";
                break;

            case 'categories':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : CategoryFilterInput)\\n{\\n  categories(filters: $filterVar)\\n  { \\n    data\\n    {\\n      id\\n      code\\n\\t\\t\\ttitle\\n  isCommercial\\n\\t\\t\\ttitle\\n   \\tsubCategories\\n\\t\\t\\t{\\n\\t\\t\\t\\tid\\n\\t\\t\\t\\tcode\\n\\t\\t\\t\\ttitle \\n\\t\\t\\t}\\n }\\n  }\\n}\",\"variables\":{\"filters\": {\"getOnlyMainCategories\": true $filter_date_modified}}}";
                break;

            case 'deleted_categories':
                $expiresBefore = !empty($params) ? $params : Carbon::now()->toDateString();
                $expiresBefore = sprintf(", \"expiresBefore\":\"%s\"", $expiresBefore);
                $bodyValue = "{\"query\":\"query($filterVar : CategoryFilterInput)\\n{\\n  categories(filters: $filterVar)\\n  { \\n    data\\n    {\\n      id\\n      code\\n\\t\\t\\ttitle\\n     \\tsubCategories\\n\\t\\t\\t{\\n\\t\\t\\t\\tid\\n\\t\\t\\t\\tcode\\n\\t\\t\\t\\ttitle \\n\\t\\t\\t}\\n }\\n  }\\n}\",\"variables\":{\"filters\": {\"getOnlyMainCategories\": true $expiresBefore}}}";
                break;

            case 'branches':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : OfficeFilterInput) {\\n \\toffices(filters: $filterVar)  {\\n\\t\\tcount,\\n\\t\\tdata {\\n\\t\\t\\ttitle,\\n\\t\\t\\tdisplayOrder,\\n\\t\\t\\tcontactNumber, \\n\\t\\t\\temail,\\n\\t\\t\\taddress, \\n\\t\\t\\tcoordinates, \\n\\t\\t\\texpiresOn,\\n\\t\\t\\texternalIdentifier,  \\n\\t\\t\\tslug,\\n\\t\\t\\tsystemIdentifier,\\n\\t\\t\\tagents{   \\n\\t\\t\\t\\tid \\n\\t\\t\\t}\\n     }\\n  }\\n}\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit$filter_date_modified}}}";
                break;

            case 'deleted_branches':
                $expiresBefore = !empty($params) ? $params : Carbon::now()->toDateString();
                $expiresBefore = sprintf(", \"expiresBefore\":\"%s\"", $expiresBefore);
                $bodyValue = "{\"query\":\"query($filterVar : OfficeFilterInput) {\n \toffices(filters: $filterVar)  {\n\t\tcount,\n\t\tdata {\n\t\t\ttitle,    externalIdentifier,   ,\n\t\t\tagents{   \n\t\t\t\tid \n\t\t\t}\n     }\n  }\n}\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit $expiresBefore}}}";
                break;

            case 'all_branches':
                $bodyValue = "{\"query\":\"query($filterVar : OfficeFilterInput) {\\n \\toffices(filters: $filterVar)  {\\n\\t\\tcount,\\n\\t\\tdata {\\n\\t\\t\\ttitle,    externalIdentifier,   ,\\n\\t\\t\\tagents{   \\n\\t\\t\\t\\tid \\n\\t\\t\\t}\\n     }\\n  }\\n}\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit}}}";
                break;

            case 'all_branches_id':
                $bodyValue = "{\"query\":\"query($filterVar : OfficeFilterInput) {\n \toffices(filters: $filterVar)  {\n\t\tcount,\n\t\tdata {  externalIdentifier }\n  }\n}\",\"variables\":{\"filters\":{\"page\":$page,\"size\":$limit}}}";
                break;

            case 'deleted_agents':
                $expiresBefore = !empty($params) ? $params : Carbon::now()->toDateString();
                $expiresBefore = sprintf(", \"expiresBefore\":\"%s\"", $expiresBefore);
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {  externalIdentifier,  id , name, agentCode,  surname  }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit $expiresBefore}}}";
                break;

            case 'agents':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {\\n\\t\\t\\tdescription,\\n      slug,\\n      externalIdentifier,\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\temail,\\n      id,\\n      name,\\tagentCode,\\n   surname,\\n\\t\\t\\tdesignation,\\n\\t\\t\\tareasOfExpertise,\\n\\t\\t\\tcalendlyId,\\n\\t\\t\\tlicences,\\n\\t\\t\\twhatsAppNumber,\\n\\t\\t\\tcontactNumber,\\n\\t\\t\\tsecondaryContactNumber\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\toffice\\n\\t\\t\\t{\\n\\t\\t\\t\\tid,\\n\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\texternalIdentifier,\\n\\t\\t\\t\\tcontactNumber\\n\\t\\t\\t},\\n\\t\\t\\tprofileImage\\n      {\\n          url        \\n      }\\n     }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit $filter_date_modified}}}";
                break;

            case 'get_all_agents_id':
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {id }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit}}}";
                break;

            case 'get_all_agents_codes':
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {agentCode }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit}}}";
                break;

            case 'agents_to_update':
                $filter_date_modified = !empty($params) ? ", \"modifiedAfter\":\"$params\"" : "";
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {id }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit $filter_date_modified}}}";
                break;

            case 'agents_filter_by_code':
                $agent_codes = !empty($params) ? ", \"agentCodes\":[$params]" : "";
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {\\n\\t\\t\\tdescription,\\n      slug,\\n      externalIdentifier,\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\temail,\\n      id,\\n      name,\\tagentCode,\\n   surname,\\n\\t\\t\\tdesignation,\\n\\t\\t\\tareasOfExpertise,\\n\\t\\t\\tcalendlyId,\\n\\t\\t\\tlicences,\\n\\t\\t\\twhatsAppNumber,\\n\\t\\t\\tcontactNumber,\\n\\t\\t\\tsecondaryContactNumber\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\toffice\\n\\t\\t\\t{\\n\\t\\t\\t\\tid,\\n\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\texternalIdentifier,\\n\\t\\t\\t\\tcontactNumber\\n\\t\\t\\t},\\n\\t\\t\\tprofileImage\\n      {\\n          url        \\n      }\\n     }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit $agent_codes}}}";
                break;

            case 'agents_filter_by_id':
                $agent_identifiers = !empty($params) ? ", \"identifiers\":[$params]" : "";
                $bodyValue = "{\"query\":\"query($filterVar : AgentFilterInput) {\\n  agents(filters: $filterVar) {\\n    count\\n    data\\n    {\\n\\t\\t\\tdescription,\\n      slug,\\n      externalIdentifier,\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\temail,\\n      id,\\n      name,\\tagentCode,\\n   surname,\\n\\t\\t\\tdesignation,\\n\\t\\t\\tareasOfExpertise,\\n\\t\\t\\tcalendlyId,\\n\\t\\t\\tlicences,\\n\\t\\t\\twhatsAppNumber,\\n\\t\\t\\tcontactNumber,\\n\\t\\t\\tsecondaryContactNumber\\n\\t\\t\\tofficeIdentifier,\\n\\t\\t\\toffice\\n\\t\\t\\t{\\n\\t\\t\\t\\tid,\\n\\t\\t\\t\\ttitle,\\n\\t\\t\\t\\texternalIdentifier,\\n\\t\\t\\t\\tcontactNumber\\n\\t\\t\\t},\\n\\t\\t\\tprofileImage\\n      {\\n          url        \\n      }\\n     }\\n  }\\n}\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit $agent_identifiers}}}";
                break;

            case 'properties_by_refs':
                $bodyValue = "{\"query\":\"query($filterVar : PropertyFilterInput) {\\n properties(filters: $filterVar) {\\n    count\\n    data\\n    {  agent { id } } } } \",\"variables\": { \"filters\": { \"referenceNumbers\": $params, \"page\": $page, \"size\": $limit } } }"; //[ \"BL1F132767\", \"BL1F135150\", \"BL1F136027\" ]
                break;

            case 'location_related':
                $bodyValue = "{\"query\":\"query($filterVar: PropertyFilterInput) {\\n properties(filters: $filterVar) {\\n count,\\n\\t\\tdata {  \\n\\t\\t\\treferenceNumber\\n\\t\\t\\tlocality {id\\n\\t\\t\\t\\ttitle\\n\\t\\t\\t\\tdescription \\n\\t\\t\\t\\tregion {\\n\\t\\t\\t\\t\\tid\\n\\t\\t\\t\\t\\ttitle \\n\\t\\t\\t\\t}\\n\\t\\t\\t\\tcountry {code\\n\\t\\t\\t\\t\\tid\\n\\t\\t\\t\\t\\ttitle\\n\\t\\t\\t\\t}\\n\\t\\t\\t} \\n\\t\\t}\\n\\t}\\n}\\n\",\"variables\":{ \"filters\": { \"page\": $page, \"size\": $limit }}}";
                break;

            case 'country':
                $bodyValue = "{\"query\":\"query($filterVar : CountryFilterInput) {\\n  countries(filters: $filterVar) { \\n    data\\n    {\\n      id\\n      title\\n      code    \\n    }}} \",\"variables\":{}}";
                break;

            case 'region':
                $bodyValue = "{\"query\":\"query($filterVar : RegionFilterInput) {\\n  regions(filters: $filterVar) { \\n    data\\n    {\\n      id\\n      title\\n      code    \\n    }}} \",\"variables\":{}}";
                break;

            case 'localities':
                $bodyValue = "{\"query\":\"query($filterVar : LocalityFilterInput){\\n  localities(filters: $filterVar) { \\n    data\\n    {\\n      id\\n      title\\n      code\\n      description\\n      region\\n      {\\n        title\\n        code\\n        id\\n      }\\n      country\\n      {\\n        id\\n        title\\n        code\\n\\t\\t\\t} }}}\\n \",\"variables\":{}}";
                break;
        }
        return $bodyValue;
    }

    public static function notionCreateRequestBody($property, $limit = 10, $lastSyncDate = null)
    {

        $bodyValue = "";
        switch ($property) {
            case 'agents':
                $bodyValue = array(
                    "filter" => array(
                        "property" => "Assigned Agent",
                        "people" => array(
                            "is_not_empty" => true
                        )
                    ),
                    "page_size" => $limit
                );
                break;
            case 'locality_and_region':
                $bodyValue = array(
                    "filter" => array(
                        "or" => array(
                            array(
                                "property" => "Locality",
                                "multi_select" => array(
                                    "is_not_empty" => true
                                )
                            ),
                            array(
                                "property" => "Region",
                                "select" => array(
                                    "is_not_empty" => true
                                )
                            )
                        )
                    ),
                    "page_size" => $limit
                );
                break;
            case 'property_type':
                $bodyValue = array(
                    "filter" => array(
                        "property" => "Property Type ",
                        "select" => array(
                            "is_not_empty" => true
                        )
                    ),
                    "page_size" => $limit
                );
                break;
            case 'f136f7b684f54705b2ba70ad8cea739c': // letting property
                $bodyValue = isset($lastSyncDate) ? array(
                    "filter" => array(
                        "and" => array(
                            array(
                                "property" => "LL#",
                                "title" => array(
                                    "is_not_empty" => true
                                )
                            ),
                            array(
                                "or" => array(
                                    array(
                                        "property" => "Created time",
                                        "date" => array(
                                            "after" => $lastSyncDate
                                        )
                                    ),
                                    array(
                                        "property" => "Last edited time",
                                        "date" => array(
                                            "after" => $lastSyncDate
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    "page_size" => $limit
                ) :  array(
                    "filter" => array(
                        "property" => "LL#",
                        "title" => array(
                            "is_not_empty" => true
                        )
                    ),
                    "page_size" => $limit
                );;
                break;
            case '0b0cba7ad93247c2a76c2808d727ef52': // sale property
                $bodyValue = array(
                    "filter" => array(
                        "property" => "S#",
                        "title" => array(
                            "is_not_empty" => true
                        )
                    ),
                    "page_size" => $limit
                );
                break;
            case 'e9ee7e96954446f1a68f54e7da3418de': // sale property
                $bodyValue = array(
                    "filter" => array(
                        "property" => "Commercial Address",
                        "title" => array(
                            "is_not_empty" => true
                        )
                    ),
                    "page_size" => $limit
                );
                break;
        }

        return $bodyValue;
    }

    public static function removeEmojis($text)
    {

        $clean_text = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{23E9}-\x{23F3}\x{3299}\x{3297}\x{3030}\x{2B05}-\x{2B07}\x{2B1B}\x{2B1C}\x{2B50}\x{2B55}]/u', ' ', $text);

        return $clean_text;
    }


    public static function recursiveTrim($data)
    {
        $trimmedArray = [];

        foreach ($data as $key => $value) {
            $trimmedKey = trim($key);
            if (is_array($value)) {
                $trimmedArray[$trimmedKey] = self::recursiveTrim($value);
            } elseif (is_string($value)) {
                $trimmedArray[$trimmedKey] = trim($value);
            } else {
                $trimmedArray[$trimmedKey] = $value;
            }
        }

        return $trimmedArray;
    }

    public static function handleDeletedEntityOnNotion($entityClass, $entityName, $ids)
    {
        $ids = array_unique($ids);

        Utilities::message("Before deletion");

        $entities = $entityClass::whereNotIn('old_id', $ids)->get();
        Utilities::message("Found match {$entityName} ids, deleted in notion count: " . $entities->count());

        $hasProbableIssues = false;

        foreach ($entities as $entity) {
            Utilities::message($entity->old_id);
        }


        if (empty($ids)) {
            $hasProbableIssues = true;
            Utilities::message("ABORT DELETION: Got ZERO LISTS of {$entityName} from Data Provider");
            return false;
        }

        if ($hasProbableIssues === false) {
            if ($entities->count() == 0) {
                Utilities::message("NOTHING TO DELETE! {$entityName} data are up to date.");
            } else {
                $entityClass::whereNotIn('old_id', $ids)->delete();
                $entities = $entityClass::whereNotIn('old_id', $ids)->get();

                Utilities::message("After deletion");
                Utilities::message("Recheck found match {$entityName} ids, persisted count: " . $entities->count());
            }
            return true;
        }
    }
    public static function getDefaultPropertyFileAttributes($type)
    {
        return [
            'file_id'            => null,
            'property_id'        => null,
            'file_name'          => 'default.webp',
            'file_type'          => $type,
            'sequence_no'        => null,
            'mime'               => 'image/webp',
            'original_file_name' => 'default.webp',
            'url_field'          => config('url.property_thumbnail'),
            'image_status_field' => null,
            'seo_url'            => null,
            'orig_path'          => config('url.property_thumbnail')
        ];
    }

    public static function deleteFilesExceedsSequence($property_id, $last_sequence_number)
    {

        $files = DB::table('files')->where('property_id', $property_id)->where('sequence_no_field', '>', $last_sequence_number)->get();

        foreach ($files as $file) {
            Utilities::message("checking files exceeds sequence:" . $property_id . ": sec: " . $file->sequence_no_field);
            $file_path = public_path(tenant('id') . "/image/property/" . $file->property_id . "/" . $file->file_name_field);
            try {
                if (File::exists($file_path)) {
                    File::delete($file_path);
                }

                $deleted = DB::table('files')->where('property_id', $property_id)->where('sequence_no_field', $file->sequence_no_field)->delete();

                if ($deleted) {
                    Utilities::message("done deleting files exceeds sequence:" . $property_id . ": sec: " . $file->sequence_no_field);
                }
            } catch (\Exception $e) {
                Log::info("Error deleting files:" . json_encode($e));
                echo ("Error deleting files, check logs\n");
            }
        }
    }
}
