<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IBranchSyncService;
use App\Components\Services\IBranchService;
use App\Components\Services\IAgentService;
use App\Components\Services\Impl\SyncUtilityService; 
use Illuminate\Support\Facades\Log; 
use App\Models\DataImport;
use Exception;

class BranchSyncService extends SyncUtilityService implements IBranchSyncService {
    
    private $_branchService;
    private $_agentService;

    public function __construct(IBranchService $branchService, IAgentService $agentService) 
    {
        parent::__construct();
        $this->_branchService  = $branchService;
        $this->_agentService   = $agentService;
    }

    public function bulk($raw_datas, $webhook)
    {   
        $update_counts = 0;
        foreach($raw_datas as $raw_data) {
            $update_counts += $this->process($raw_data, $webhook);
        }
        return $update_counts;
    }
    
    public function process( $raw_data, $webhook = null)
    {
        if(!empty($webhook ))
            $webhook = DataImport::where('id', $webhook->id)->first();
        try {
           
            $transformed_data = $this->transform($raw_data); 
     
            $this->_branchService->createUpdateBranch( $transformed_data ); 
            if(isset($raw_data->agents) && count($raw_data->agents) > 0) {
                $this->_agentService->updateAgents($raw_data->agents, [
                    "branch_id_field" => $transformed_data['id']
                ]); 
            } 
        } catch (Exception $e ) {
            Log::debug($e->getMessage());
            if(!empty($webhook ))
                $webhook->saveException($e);
            return 0;
        }
        return 1;
    }

    public function transform($data) 
    { 
        return [
            'id' => $data->externalIdentifier,
            'name' => $data->title ?? null,
            'slug' =>  $data->slug ?? null,
            'email' =>  $data->email ?? null,
            'contact_number' =>  $data->contactNumber ?? null,
            'address' => $data->address ?? null,
            'coordinates' => $data->coordinates ?? null,
            'display_order' => $data->displayOrder ?? null
        ];
    }
}