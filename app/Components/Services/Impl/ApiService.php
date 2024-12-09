<?php

namespace App\Components\Services\Impl;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Traits\ShouldExecuteTenants;
use App\Components\Services\IApiService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class ApiService implements IApiService
{
    use TenantAwareCommand, ShouldExecuteTenants;

    public $client;
    public $headers;

    public function __construct()
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    public function graphQLRequest($request_body, $page = 1, $limit = 10)
    {
        $this->headers['X-System-Identifier'] = config('cms.system_identifier');

        $handlerStack = HandlerStack::create();

        $maxRetries = 3;
        $handlerStack->push(Middleware::retry(
            function ($retries, $request, $response = null, $exception = null) use ($maxRetries) { 
                if ($retries >= $maxRetries) {
                    return false;
                }

                if ($response && in_array($response->getStatusCode(), [500, 502, 503, 504])) {
                    return true;
                }
                if ($exception instanceof RequestException) {
                    return true;
                }
                return false;
            },
            function ($retries) {
                return 100 * $retries;
            }
        ));

        $this->client = new Client([
            'headers' => $this->headers,
            'timeout' => 60,
            'handler' => $handlerStack,
        ]);

        try {

            $result = $this->client->request('POST', config('cms.url'), [
                'body' => $request_body
            ]);


            return json_decode($result->getBody()->getContents());
        } catch (RequestException $e) {

            Log::error('GraphQL Request Failed', [
                'message' => $e->getMessage(),
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No response',
                'body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body'
            ]);

            $this->throwError($e);
        }
        //added a retry mechanism on graphql requests, consider removing retry mechanisms on commands that uses this function.
    }


    public function sendRequest($endpoint, $data = [], $method = 'POST', $format = 'json')
    {
        try {

            $headers = [
                'User-Agent'    => 'browser/1.0',
            ];

            if ($format == 'json') {
                $headers['Accept'] = 'application/json';
                $headers['Content-Type'] = 'application/json';
            }

            $args = [
                'headers' => $headers,
            ];

            if ($format == 'multipart') {
                $args['multipart'] = $data;
            } else {
                $args['json'] = $data;
            }

            $result = $this->client->request($method, $endpoint, $args);

            return json_decode($result->getBody()->getContents(), true);
        } catch (Exception $e) {
            $this->throwError($e);
        }
    }

    public function getRequest($endpoint, $query = [])
    {
        try {
            $headers = [
                'User-Agent'    => 'browser/1.0',
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ];

            $result = $this->client->request(
                'GET',
                $endpoint,
                [
                    'headers' => $headers,
                    'query' => $query
                ]
            );

            return json_decode($result->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $this->throwError($e);
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
