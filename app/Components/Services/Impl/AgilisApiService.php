<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IApiService;
use App\Exceptions\ProcessException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use App\Constants\Http\StatusCode;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Passive\Utilities;
use App\Components\Services\ILandlordService;

class AgilisApiService implements IApiService
{
    public $client;
    public $headers;
    private $_landlord_service;

    public function __construct(ILandlordService $landlord_service)
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
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

    public function login($endpoint, $data = [], $method = 'POST', $format = 'json')
    {
        try {
            $headers = [
                'User-Agent'    => 'browser/1.0',
            ];

            if ($format == 'json') {
                $headers['Accept'] = 'application/json';
                $headers['Content-Type'] = 'application/json';
            }
            $this->client = new Client([
                'headers' => $this->headers,
                'timeout' => 60
            ]);

            $args = [
                'headers' => $headers,
            ];

            if ($format == 'multipart') {
                if (!empty($data))
                    $args['multipart'] = $data;
            } else {
                if (!empty($data))
                    $args['json'] = $data;
            }

            $result = $this->client->request($method, $endpoint, $args);

            return json_decode($result->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $this->throwError($e);
        }
    }

    public function sendRequest($endpoint, $data = [], $method = 'POST', $format = 'json')
    {
        return $this->processSendRequest($endpoint, $data, $method, $format, 0);
    }

    private function processSendRequest($endpoint, $data = [], $method = 'POST', $format = 'json', $retry = 0)
    {
        try {
            $bearerToken = $this->getAccessToken();
            $headers = [
                'User-Agent'    => 'browser/1.0',
            ];

            if ($format == 'json') {
                $headers['Accept'] = 'application/json';
                $headers['Content-Type'] = 'application/json';
                $headers['Authorization'] = 'Bearer ' . $bearerToken;
            }
            $this->client = new Client([
                'headers' => $this->headers,
                'timeout' => 60
            ]);

            $args = [
                'headers' => $headers,
            ];

            if ($format == 'multipart') {
                if (!empty($data))
                    $args['multipart'] = $data;
            } else {
                if (!empty($data))
                    $args['json'] = $data;
            }

            $response = $this->client->request($method, $endpoint, $args);
            // Utilities::message( print_r([$method,$args], true) );
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $content = $body->getContents();
            // Utilities::message( print_r($content, true) );
            // Process the response as needed
            return  [
                'status_code' => $statusCode,
                'status' => 'success',
                'data' => json_decode($content, true),
            ];

            // return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            if ($retry < 1 && in_array($e->getResponse()->getStatusCode(), [401])) {
                // Utilities::message("unauthorize: retry via refresh token");
                $this->refreshToken();
                $this->processSendRequest($endpoint, $data, $method, $format, ($retry + 1));
            } else {
                $this->throwError($e);
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorContent = $response->getBody()->getContents();
                return [
                    'status_code' => $statusCode,
                    'status' => 'error',
                    'message' => $errorContent,
                ];
            } else {
                return [
                    'status_code' => $statusCode,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    public function getRequest($endpoint, $query = [])
    {
        return $this->processRequest($endpoint, $query, 0);
    }

    private function processRequest($endpoint, $query = [], $retry = 0)
    {
        try {
            $bearerToken = $this->getAccessToken();

            $headers = [
                'User-Agent'    => 'browser/1.0',
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
            ];
            $this->client = new Client([
                'headers' => $this->headers,
                'timeout' => 60
            ]);

            $result = $this->client->request(
                'GET',
                $endpoint,
                [
                    'headers' => $headers,
                    'query' => $query
                ]
            );

            if (null != $result->getHeader('X-Pagination')) {
                $pagination = json_decode($result->getHeader('X-Pagination')[0]);
                $response["count"] =  $pagination->count ?? null;
                $response["total_pages"] =  $pagination->totalPages ?? null;
                $response["data"] = json_decode($result->getBody()->getContents(), true);
            } else {
                $response = json_decode($result->getBody()->getContents(), true);
            }

            return $response;
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 404) {
                return [];
            } else if ($retry == 0 && in_array($statusCode, [401])) {
                // Utilities::message("unauthorize: retry via refresh token");
                $this->refreshToken();
                $this->processRequest($endpoint, $query, ($retry + 1));
            } else if ($retry == 1 && in_array($statusCode, [401])) {
                // Utilities::message("unauthorize: retry via refresh token");
                $this->generateToken();
                $this->processRequest($endpoint, $query, ($retry + 1));
            } else {
                $this->throwError($e);
            }
        }
    }


    private function getAccessToken()
    {
        $bearerToken = config("cms.access_token") ?? null;

        if (empty($bearerToken)) {
            $bearerToken = $this->generateToken();
        } else {
            // Utilities::message("GOT token from cms.access_token"); 
        }

        return $bearerToken;
    }

    private function generateToken()
    {
        // Utilities::message("no token from cms.access_token, getting token from agilis");
        // Utilities::message(print_r([
        //     config("cms.url") . "/api/user/token", 
        //     [
        //         "Username" => tenant("username"),
        //         "Password" => tenant("password"),
        //     ],
        // ], true));
        $result = $this->login(
            config("cms.url") . "/api/user/token",
            [
                "Username" => tenant("username"),
                "Password" => tenant("password"),
            ],
        );

        $data = [
            'access_token'  => $result['accessToken'],
            'refresh_token' => $result['refreshToken']
        ];

        // Utilities::message(print_r( $data, true));
        $this->_landlord_service->updateTenantAccessToken($data, tenant('id'));

        return $result['accessToken'];
    }


    private function refreshToken()
    {
        try {
            $refresh_token = config("cms.refresh_token") ?? null;

            if (empty($refresh_token)) {
                // Utilities::message("no refresh_token from cms.refresh_token");
                return $this->getAccessToken();
            } else {
                // Utilities::message("Get new token via cms.refresh_token"); 
                $result = $this->sendRequest(
                    config("cms.url") . "/api/user/refreshtoken",
                    $refresh_token
                );

                $data = [
                    'access_token'  => $result['accessToken'],
                    'refresh_token' => $result['refreshToken']
                ];

                $this->_landlord_service->updateTenantAccessToken($data, tenant('id'));
                return $result['accessToken'];
            }
        } catch (Exception $e) {
            Utilities::message(json_encode($e));
            Utilities::notifySlack(ProcessExceptionMessage::AGILIS_UNABLE_TO_RETRIEVE_TOKEN, StatusCode::HTTP_INTERNAL_SERVER_ERROR);
            return null;
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
