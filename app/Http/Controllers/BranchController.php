<?php

namespace App\Http\Controllers;

use App\Components\Services\IBranchService;  
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class BranchController extends ApiController
{
    private $_branchService;
    
    public function __construct(  IBranchService $branchService )
    {
        $this->_branchService = $branchService; 
    }

    public function index(Request $request)
    { 
        $limit = $request->get( 'limit' ) ?? '100';

        $result = $this->_branchService->getBranches($limit);

        return $this->respond([
            'paginator' => $result['paginator'],
            'data' =>$result['data']
        ]);    
    } 

    public function view($id) 
    { 
        $result = $this->_branchService->getBranch($id);
 
        return $this->respond($result);   
    }
}