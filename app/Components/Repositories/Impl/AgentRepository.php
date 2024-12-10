<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IAgentRepository;
use App\Models\Consultant;


class AgentRepository implements IAgentRepository
{
    public function createAgent($data)
    {
        return Consultant::create($data);
    }

    public function updateAgent($id, $data)
    {
        return Consultant::where("id", $id)->update($data);
    }

    public function updateAgents($id, $data)
    {
        return Consultant::whereIn("old_id", $id)->update($data);
    }

    public function getAgentByOldID($id)
    {
        return Consultant::where("old_id", $id)->first();
    }

    public function getAgentByID($id)
    {
        return Consultant::where("id", $id)->first();
    }

    public function searchAgent($filters, $limit = 10)
    {
        $consultant = Consultant::query()->isPublic()
            ->with('branch')
            ->withCount('properties');
        foreach ($filters as $key => $value) {
            if (isset($value) && $value) {
                switch ($key) {
                    case 'full_name_field':
                        $consultant->where($key, 'like', '%' . $value . '%');
                        break;

                    case 'branch_id_field':
                        $branches = array_map('trim', explode('-', $value));
                        $consultant->whereIn($key, $branches);
                        break;

                    case 'property_type_id_field':
                        $property_types = array_map('trim', explode('-', $value));
                        $consultant->whereHas('properties', function ($query) use ($key, $property_types) {
                            $query->whereIn($key, $property_types);
                        });
                        break;

                    case 'locality_id_field':
                        $localties = array_map('trim', explode('-', $value));
                        $consultant->whereHas('properties', function ($query) use ($key, $localties) {
                            $query->whereIn($key, $localties);
                        });
                        break;
                }
            }
        }

        return $consultant->paginate($limit);
    }
}
