<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IBranchService; 
use App\Components\Repositories\IBranchRepository; 
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Models\DTO\BranchDTO;

class BranchService implements IBranchService {

    private $_branchRepository;

    public function __construct(IBranchRepository $branchRepository)
    {
        $this->_branchRepository = $branchRepository;
    }

    public function createUpdateBranch($data)
    {  
        return $this->_branchRepository->createUpdateBranch($data);
    }

    public function getBranches($limit=10)
    {
        $branches_repo = $this->_branchRepository->getBranches($limit);
        $branches = [];
        foreach( $branches_repo as $branch_repo) {
            $branches[] = new BranchDTO(
                $branch_repo->id, 
                $branch_repo->name, 
                $branch_repo->slug, 
                $branch_repo->email, 
                $branch_repo->contact_number, 
                $branch_repo->address, 
                $branch_repo->coordinates,
                $branch_repo->display_order
            );
        }
        return [
            'paginator' => [
                'total_count'  => $branches_repo->total(),
                'total_pages'  => $branches_repo->lastPage(),
                'current_page' => $branches_repo->currentPage(),
                'limit'        => $branches_repo->perPage(),
            ],
            'data' => $branches
        ];  
    }

    public function getBranch( $id )
    {

        try{
            $branch_repo = $this->_branchRepository->getBranch($id);
            
            return  new BranchDTO(
                $branch_repo->id, 
                $branch_repo->name, 
                $branch_repo->slug, 
                $branch_repo->email, 
                $branch_repo->contact_number, 
                $branch_repo->address, 
                $branch_repo->coordinates,
                $branch_repo->display_order
            );
        } catch ( \Exception $e ) {
            throw new ProcessException(
                ProcessExceptionMessage::BRANCH_NOT_EXIST,
                StatusCode::HTTP_NOT_FOUND
            );
        }
    }  
}
 