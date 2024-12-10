<?php
/**
 * API5
 */

namespace App\Http\Controllers;

use App\Niu\Transformers\PropertyAuditTransformer;
use App\PropertyAudit;
use App\property_status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyAuditsController extends ApiController {
	
	/**
	 * @var PropertyAuditTransformer
	 */
	protected $PropertyAuditTransformer;

	/**
	 * @var array
	 */
	protected $apiMethods = [
		'index' => [
			'keyAuthentication' => true
		],
	];

	/**
	 * @param PropertyAuditTransformer $PropertyAuditTransformer
	 */
	public function __construct( PropertyAuditTransformer $PropertyAuditTransformer ) {
		// parent::__construct();
		$this->propertyTypeTransformer = $PropertyAuditTransformer;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index(Request $request) {
		$property_audit_query = PropertyAudit::query();
		if($created_after_date = $request->get('created_after_date')){
			$date_formatted = date('Y-m-d H:i',strtotime($created_after_date));
			$property_audit_query->where(
				DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d %H:%i')"),
				'>',
				$date_formatted
			);
		}

		$results 				= $property_audit_query->get()->all();
		$results_transformed 	= $this->propertyTypeTransformer->transformCollection($results);
		
		return $this->respond($results_transformed);
	}

}