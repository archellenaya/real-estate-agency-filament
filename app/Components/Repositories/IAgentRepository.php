<?php

namespace App\Components\Repositories;

interface IAgentRepository
{
    public function getAgentByID($id);

    public function createAgent($data);

    public function updateAgent($id, $data);

    public function updateAgents($id, $data);

    public function getAgentByOldID($id);

    public function searchAgent($filters, $limit);
}
