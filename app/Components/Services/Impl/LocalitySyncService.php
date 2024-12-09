<?php

namespace App\Components\Services\Impl;

use App\Components\Services\ILocalitySyncService;
use App\Components\Services\Impl\SyncUtilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Repositories\ILocalityRepository;
use App\Models\DataImport;
use Exception;

class LocalitySyncService extends SyncUtilityService implements ILocalitySyncService {
    
    private $_localityRepository;

    public function __construct(ILocalityRepository $localityRepository) 
    {
        parent::__construct();
        $this->_localityRepository  = $localityRepository;
    }

    public function bulk($raw_datas, $webhook)
    {   
        $update_counts = 0;
        foreach($raw_datas as $raw_data) {
            $update_counts += $this->process($raw_data, $webhook);
        }
        return $update_counts;
    }
    
    public function process( $raw_data, $webhook)
    {
        $webhook = DataImport::where('id', $webhook->id)->first();
        try {
            $transformed_data = $this->transform($raw_data);
            $locality = $this->_localityRepository->getLocalityByOldID($transformed_data['old_id']);
         
            if(isset($locality) && $locality) {
                $locality->update($transformed_data);
                Log::debug("Updated locality: " . $locality->id);
            } else {
                $this->_localityRepository->createLocality($transformed_data);
                Log::debug("Created locality: " . $transformed_data['old_id']);
            }
        } catch (Exception $e ) {
            Log::debug($e->getMessage());
            $webhook->saveException($e);
            return 0;
        }
        return 1;
    }

    public function transform($data) 
    {
        return [
            'old_id' => $data->id,
            'locality_name' => $data->name,
            'description' => $this->extractValue($data->description),
            'region' => $this->extractValue($data->region),
            'post_code' => $this->extractValue($data->post_code),
            'status' => $data->status ?? 0,
        ];
    }
}