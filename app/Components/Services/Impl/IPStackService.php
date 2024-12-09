<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IIPStackService;
use Illuminate\Support\Facades\Log;

class IPStackService implements IIPStackService {

    
    public function getIPInformation(string $ip){
        $apiKey = $this->getIPStackAPIKey();
        if(empty($apiKey) === true){
            Log::error("Missing API Key for IPStackService, check env file for IPSTACK_API_KEY");
            return null;
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', "https://api.ipstack.com/$ip?access_key=$apiKey");
        
        $response_decoded = null;
        try
        {
            $response = $response->getBody()->getContents();
            $response_decoded = json_decode($response);
        }
        catch(\Exception $e)
        {
            Log::error("Error while fetching response from IP Stack API, given ip:$ip, exception:".$e->getMessage());
        }

        return $response_decoded;
   }

   private function getIPStackAPIKey(){
       $apiKey = env('IPSTACK_API_KEY');
       return $apiKey;
   }
}