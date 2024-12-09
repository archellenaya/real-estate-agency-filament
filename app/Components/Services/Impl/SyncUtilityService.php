<?php

namespace App\Components\Services\Impl;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class SyncUtilityService
{

    public function __construct() {}

    public function extractValue($data)
    {
        if (is_array($data)) {
            if (count($data) == 0)
                return null;
            if (count($data) == 1)
                return $data[0];
        }

        return $data;
    }

    public function getID($table, $compare_fields, $data, $pluck_field = 'id')
    {
        $query_id = DB::table($table);

        foreach ($compare_fields as $field => $value) {
            $query_id->where($field, $value);
        }

        $id = $query_id->pluck($pluck_field)->first();

        if (isset($id) && $id) {
            return $id;
        } else {
            $data['created_at'] = Carbon::now();
            $data['updated_at'] = Carbon::now();

            DB::table($table)->updateOrInsert(
                $compare_fields,
                $data
            );

            return $this->getID($table, $compare_fields, $data, $pluck_field);
        }
    }

    public function update($table, $compare_fields, $data, $pluck_field = 'id')
    {
        $query_id = DB::table($table);

        foreach ($compare_fields as $field => $value) {
            $query_id->where($field, $value);
        }

        $id = $query_id->pluck($pluck_field)->first();

        if (isset($id) && $id) {
            DB::table($table)->where($pluck_field, $id)->update($data);
        }
    }

    public function generate_consultant_id_old($consultant_name, $char_to_take_in_firstname = 1, $char_to_take_in_lastname = 2)
    {
        $splitted_name = explode(' ', $consultant_name);
        $firstname = $splitted_name[0];
        $lastname  = $splitted_name[count($splitted_name) - 1];

        $newID = $this->getChars($firstname, $char_to_take_in_firstname) . $this->getChars($lastname, $char_to_take_in_lastname);
        $id = DB::table('consultants')->where('id', $newID)->first();
        if (isset($id) && $id) {
            return $this->generate_consultant_id($consultant_name, $char_to_take_in_firstname + 1, $char_to_take_in_lastname + 1);
        } else
            return strtoupper($newID);
    }

    public function generate_consultant_id($consultant_name, $char_to_take = 1)
    {
        $splitted_name = explode(' ', $consultant_name);
        $newID = "";
        $count = 1;
        foreach ($splitted_name as $sylable) {
            if ($char_to_take == 2) {
                $newID .= $this->getChars($sylable, $count == 1 ? $char_to_take : 1);
            } else if ($char_to_take == 3) {
                $newID .= $this->getChars($sylable, $count == 1 ? $char_to_take - 1 : 1);
            } else if ($char_to_take >= 4) {
                $newID .= $this->getChars($sylable, $count == count($splitted_name) || $count == 1 ? $char_to_take - 1 : 1);
            } else {
                $newID .= $this->getChars($sylable, 1);
            }
            $count++;
        }
        $id = DB::table('consultants')->where('id', $newID)->first();
        if (isset($id) && $id) {
            return $this->generate_consultant_id($consultant_name, $char_to_take + 1);
        } else
            return strtoupper($newID);
    }

    public function getChars($name, $char_num_to_take)
    {
        $splitted_name = str_split($name);
        $count = 0;
        $output = [];
        foreach ($splitted_name as $char) {
            if (ctype_alpha($char)) {
                $output[] = $char;
                $count++;
            }

            if ($count >= $char_num_to_take)
                break;
        }

        return implode("", $output);
    }

    function mime_content_type($filename)
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

        $file_info = pathinfo($filename);
        $ext = strtolower($file_info['extension']);

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

    protected function stripUrlQueryString($url)
    {
        $url_array = parse_url($url);
        $url = $url_array['scheme'] . '://' . $url_array['host'] . $url_array['path'];

        $url = str_replace("/styles//public", "", $url);
        return $url;
    }

    public function encoded($str)
    {
        $pos = strrpos($str, '/') + 1;
        return substr($str, 0, $pos) . rawurlencode(substr($str, $pos));
    }

    public function decoded($str)
    {
        $pos = strrpos($str, '/') + 1;
        return substr($str, 0, $pos) . rawurldecode(substr($str, $pos));
    }

    public function image_get_contents($url)
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

    public function transformPhoneNumber($phoneNumber)
    {
        // Remove +356 if it exists
        $phoneNumber = str_replace('+356', '', $phoneNumber);

        // Remove spaces
        $phoneNumber = str_replace(' ', '', $phoneNumber);

        return $phoneNumber;
    }

    public function removeHtmlAndEntities($input)
    {
        return strip_tags(html_entity_decode($input));
    }
}
