<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IAgentPropertyService;

use App\Components\Repositories\IAgentRepository;


class AgentPropertyService implements IAgentPropertyService {

    private $_agentRepository;

    public function __construct(IAgentRepository $agentRepository)
    {
        $this->_agentRepository = $agentRepository;
    }

    public function search($filters, $limit)
    {
        return $this->_agentRepository->searchAgent($filters, $limit);
    } 
}
