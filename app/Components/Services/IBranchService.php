<?php

namespace App\Components\Services;

interface IBranchService
{
    public function getBranches($limit=10);

    public function createUpdateBranch($data);

    public function getBranch( $id );
}