<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IApiService;
use App\Exceptions\ProcessException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use App\Constants\Http\StatusCode;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Passive\Utilities;
use App\Components\Services\ILandlordService;

class NotionApiService implements IApiService
{
    public $client;
    public $headers;
    private $_landlord_service;

    public function __construct(ILandlordService $landlord_service)
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Notion-Version' => '2022-06-28'
        ];
        $this->_landlord_service = $landlord_service;
    }


    public function graphQLRequest($request_body, $page = 1, $limit = 10)
    {
        $this->headers['X-System-Identifier'] = config('cms.system_identifier');

        $this->client = new Client([
            'headers' => $this->headers,
            'timeout' => 60
        ]);

        try {
            $result = $this->client->request('POST', config('cms.url'), [
                'body' => $request_body
            ]);

            return json_decode($result->getBody()->getContents());
        } catch (ClientException $e) {
            Log::debug(print_r($e->getMessage(), true));
            $this->throwError($e);
        }
    }



    public function sendRequest($endpoint, $data = [], $method = 'POST', $format = 'json')
    {
        return $this->processRequest($endpoint, $data);
    }

    public function getRequest($endpoint, $query = [])
    {
        return $this->processRequest($endpoint, $query);
    }

    private function processRequest($endpoint, $data = [], $retry = 0)
    {
        try {
            $bearerToken = config("cms.api_key");

            $headers = [
                'User-Agent'    => 'browser/1.0',
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Notion-Version' => '2022-06-28',
                'Authorization' => 'Bearer ' . $bearerToken,
            ];

            $this->client = new Client([
                'headers' => $this->headers,
                'timeout' => 60
            ]);

            $result = $this->client->request(
                'POST',
                $endpoint,
                [
                    'headers' => $headers,
                    'json' => $data
                ]
            );

            $response["data"] = json_decode($result->getBody()->getContents(), true);

            return $response;
        } catch (ClientException $e) {
            if ($retry < 1 && in_array($e->getResponse()->getStatusCode(), [401])) {
                Utilities::message("unauthorize: retry ");
            } else {
                $this->throwError($e);
            }
        }
    }

    private function throwError($e)
    {
        Log::error('Exception: ' . $e->getMessage());
        throw new ProcessException(
            json_decode($e->getMessage(), true),
            $e->getCode()
        );
    }
}
