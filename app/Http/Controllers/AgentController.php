<?php

namespace App\Http\Controllers;

use App\Components\Services\IAgentService;
use App\Exceptions\ProcessException;
use Illuminate\Http\Request;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Niu\Transformers\ConsultantTransformer;
use App\Models\Consultant;

class AgentController extends ApiController
{
    private $_agentService;
	private $_consultantTransformer;

    public function __construct(IAgentService $agentService, ConsultantTransformer $consultantTransformer)
    {
        $this->_agentService = $agentService;
        $this->_consultantTransformer = $consultantTransformer;
    }

    public function search( Request $request ) 
	{
        $name        = $request->get( 'agent-name' );
        $categories  = $request->get( 'property-type' );
        $localities  = $request->get( 'localities' );
        $branch      = $request->get( 'branch' );
        $limit       = $request->get( 'limit' ) ?? '10';
        
        $parameter = [
            'full_name_field' => $name ?? null,
            'property_type_id_field' => $categories ?? null,
            'locality_id_field' => $localities ?? null,
            'branch_id_field' => $branch ?? null,
        ];

        try {
            $agents = $this->_agentService->search($parameter, $limit);

        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
		
        return $this->respondWithPagination( $agents, [
                    'data' => $this->_consultantTransformer->transformCollection( $agents->all() )
        ]);
	}

}