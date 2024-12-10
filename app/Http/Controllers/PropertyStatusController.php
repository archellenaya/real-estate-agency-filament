<?php
/**
 * API5
 */
namespace App\Http\Controllers;


use App\Niu\Transformers\PropertyStatusTransformer;
use App\property_status;

class PropertyStatusController extends ApiController {
	/**
	 * @var PropertyStatusTransformer
	 */
	protected $PropertyStatusTransformer;

	/**
	 * @var array
	 */
	protected $apiMethods = [
		'index' => [
			'keyAuthentication' => false
		],
	];

	private $propertyTypeTransformer;

	/**
	 * @param PropertyStatusTransformer $PropertyStatusTransformer
	 */
	public function __construct( PropertyStatusTransformer $PropertyStatusTransformer ) {
		// parent::__construct();
		$this->propertyTypeTransformer = $PropertyStatusTransformer;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index() {
		$propertyTypes = property_status::all();

		return $this->respond(
			[
				'data' => $this->propertyTypeTransformer->transformCollection( $propertyTypes->all() )
			]
		);
	}
}