<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IAPIv5Service;
use Illuminate\Support\Facades\Log;

class APIv5Service implements IAPIv5Service
{

	/**
	 * API key
	 *
	 * @var string
	 */
	private $api_key = 'eb8cd5b247e1626ccce7f0cc30a5bb33a6784f94';

	/**
	 * API URL to connect with franksalt server API version 5
	 *
	 * @var string
	 */
	private $api_url = '';


	/**
	 * Current api version being used, default to 1, only 2 version's are available
	 *
	 * @var integer
	 */
	private $current_api_version = 1;

	/**
	 * Curl handle
	 *
	 * @var  \CurlHandle|false
	 */
	private $curl;

	public function __construct()
	{
		$this->curl = curl_init();
	}

	/**
	 * Close curl when the class is destroyed 
	 */
	public function __destruct()
	{
		curl_close($this->curl);
	}

	/**
	 * @param string $endpoint
	 * @param string $request_type
	 * @param int    $page
	 * @param int    $limit
	 * @param null   $content_type
	 */
	public function setRequestOperation($endpoint = '/properties', $request_type = 'GET', $page = 1, $limit = 9, $content_type = 'json', $sort = '')
	{
		if ($endpoint[0] !== '/') {
			$endpoint = '/' . $endpoint;
		}


		$url_params = array();

		if (strpos($endpoint, '?') >= 0) {
			$endpoint_exploded 	= explode('?', $endpoint);
			$endpoint 			= $endpoint_exploded[0];

			if (empty($endpoint_exploded[1]) === false) {
				foreach (explode('&', $endpoint_exploded[1]) as $val) {
					$param_values = explode('=', $val);
					if (count($param_values) === 2) {
						$url_params[$param_values[0]] = $param_values[1];
					}
				}
			}
		}

		$url = $this->getApiUrl() . $endpoint;

		if (empty($page) === false) {
			$url_params['page'] = $page;
		}
		if (empty($limit) === false) {
			$url_params['limit'] = $limit;
		}
		if (empty($sort) === false) {
			$url_params['sort'] = $sort;
		}

		if (count($url_params) > 0) {
			$url .= '?' . http_build_query($url_params, '', '&'); //&amp;
		}

		// Set url
		curl_setopt($this->curl, CURLOPT_URL, $url);
		// Set method
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request_type);
		// Set options
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		// Set headers
		$header = [
			"X-Authorization:" . $this->api_key,
			// "Accept: application/json",
		];
		if ($content_type == 'json') {
			$header[] = 'Content-Type:application/json';
		}
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);

		return $this;
	}

	/**
	 * @param $body
	 */
	public function setRequestBody($data): APIv5Service
	{
		// Set header
		// curl_setopt( $this->curl, CURLOPT_POST, 1 );
		if (is_string($data) === true) {
			$data = json_decode($data);
		}

		$postdata = json_encode($data);

		// curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $body );
		// curl_setopt( $this->curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));


		return $this;
	}

	/**
	 * @return array|stdClass
	 */
	public function getResponse($json_decode = true)
	{
		// Send the request & save response to $resp
		$response_array['response']    = curl_exec($this->curl);
		$response_array['status_code'] = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

		if (!$response_array['response']) {
			$response_array['error_no'] = curl_errno($this->curl);
			$response_array['error']    = curl_error($this->curl);
		}

		if ($json_decode == true) {
			$response_array['response'] = json_decode($response_array['response']);
		}

		return $response_array;
	}

	public function setApiVersion(int $version): APIv5Service
	{
		$this->current_api_version = $version;
		return $this;
	}

	public function getApiUrl(bool $appendVersion = true): string
	{
		return $this->api_url . ($appendVersion ? ('v' . $this->current_api_version) : '');
	}


	public function getPropertiesAudits(int $created_after_date_filter = null)
	{


		$operation = '/audits/properties' . ($created_after_date_filter ? '?created_after_date=' . date('Y-m-d H:i', $created_after_date_filter) : '');

		$request_type = 'GET';
		Log::info($operation);
		//get properties
		$apiV5Response = $this->setApiVersion(2)->setRequestOperation($operation, $request_type)->getResponse(true);

		return $apiV5Response['response'];
	}


	public function getV5Property($property_ref)
	{
		$operation = '/properties/' . $property_ref;
		$request_type = 'GET';

		//get properties
		$apiV5Response = $this->setApiVersion(1)->setRequestOperation($operation, $request_type)->getResponse(true);
		$property    = ($apiV5Response['response'] ?? null) ? ($apiV5Response['response']->data ?? null)      : null;

		return $property;
	}

	public function getAPIV5Properties(array $properties_refs, bool $getIDsOnly = false): ?array
	{
		$operation = '/properties/byRefs?refs=' . implode(',', $properties_refs) . '&getIDsOnly=' . ($getIDsOnly ? '1' : '0');
		$request_type = 'GET';

		//get properties
		$apiV5Response = $this->setApiVersion(2)->setRequestOperation($operation, $request_type)->getResponse(true);
		$properties    = ($apiV5Response['response'] ?? null) ? ($apiV5Response['response'] ?? null)      : null;

		return $properties ?? [];
	}

	public function get_apiV5_property_thumb_image(?\stdClass $property, int $width = 380, $height = 205): string
	{
		$api_images_url 				= ' ';
		$default_property_thumb_image 	= '/wp-content/themes/franksalt/assets/images/property-default.jpg';

		if (empty($property) === true) {
			return $default_property_thumb_image;
		}

		if (empty($property->files)) {
			return $default_property_thumb_image;
		}

		foreach ($property->files as $file) {
			if (empty($file->mime)) {
				continue;
			}

			if (strpos($file->mime, 'image') === false) {
				continue;
			}

			if (empty($file->file_type) || $file->file_type !== 'MainImage') {
				continue;
			}

			if (empty($file->file_name)) {
				continue;
			}

			return $api_images_url . $file->file_name . "?width=$width&height=$height";
		}

		return $default_property_thumb_image;
	}
}
