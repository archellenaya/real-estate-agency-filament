<?php

namespace App\Components\Repositories;

interface IBranchRepository
{ 
    public function getBranches($limit=10);

    public function getBranch( $id );
    
    public function createUpdateBranch($data);  
}
