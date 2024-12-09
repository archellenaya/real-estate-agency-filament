<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IAgentService;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Components\Repositories\IAgentRepository;
use App\Constants\Components\FormValues;
use Illuminate\Support\Facades\Log;

class  AgentService implements IAgentService {

    private $_agentRepository;

    public function __construct(IAgentRepository $agentRepository)
    {
        $this->_agentRepository = $agentRepository;
    }

    public function updateAgents($object_ids, $data) 
    { 
        $agent_ids = [];
        foreach($object_ids as $obj) {
            $agent_ids[] = $obj->id ?? null;
        } 
        return $this->_agentRepository->updateAgents($agent_ids, $data);
    }

    public function getAgentByID($id) 
    {
        return $this->_agentRepository->getAgentByID($id);
        
    }

    public function search($filters, $limit)
    {
        return $this->_agentRepository->searchAgent($filters, $limit);
    } 
}
