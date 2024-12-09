<?php 

namespace App\Components\Services;

interface IApiService
{
    public function graphQLRequest($entity, $page = 1, $limit=10);

    public function sendRequest($endpoint, $data = [], $method = 'POST', $format = 'json');

    public function getRequest($endpoint, $query = []);
} 