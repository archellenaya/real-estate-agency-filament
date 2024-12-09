<?php

namespace App\Components\Services;

interface IAgentService
{
    public function getAgentByID($id);

    public function updateAgents($object_ids, $data);

    public function search($filters, $limit);
}