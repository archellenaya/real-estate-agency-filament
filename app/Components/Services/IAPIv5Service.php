<?php
namespace App\Components\Services;

interface IAPIv5Service
{
    public function setRequestOperation( $endpoint = '/properties', $request_type = 'GET', $page = 1, $limit = 9, $content_type = 'json', $sort = '' );

    public function setRequestBody( $data );

    public function getResponse();

    public function setApiVersion(int $version);

    public function getApiUrl();

    public function getV5Property($property_ref);
}