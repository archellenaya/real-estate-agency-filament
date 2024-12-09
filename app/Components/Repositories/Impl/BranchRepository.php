<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IBranchRepository;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class BranchRepository implements IBranchRepository
{
    public function getBranches($limit=10)
    { 
        
        return Branch::orderBy('display_order', 'ASC')->paginate($limit);
    } 

    public function getBranch( $id )
    {
        return Branch::findOrFail($id);
    }

    public function createUpdateBranch($data)
    { 
        return Branch::updateOrCreate([
            'id' => $data['id'] ?? null
        ],[ 
            'name' => $data['name'] ?? null, 
            'slug' => $data['slug'] ?? null, 
            'email' => $data['email'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'coordinates' => $data['coordinates'] ?? null,
            'display_order' => $data['display_order'] ?? null
        ]);
    }  
} 
