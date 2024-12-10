<?php

namespace App\Http\Controllers;

/**
 * API5
 */

use App\PropertyType;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Niu\Transformers\PropertyTypeTransformer;

/**
 * Class PropertyTypeController
 *
 * @package App\Http\Controllers
 */
class PropertyTypeController extends ApiController
{

	/**
	 * @var PropertyTypeTransformer
	 */
	protected $propertyTypeTransformer;

	/**
	 * @var array
	 */
	protected $apiMethods = [
		'index'              => [
			'keyAuthentication' => FALSE
		],
		'commercial'         => [
			'keyAuthentication' => FALSE
		],
		'residential'        => [
			'keyAuthentication' => FALSE
		],
		'get_property_types' => [
			'keyAuthentication' => FALSE
		],
	];

	/**
	 * @param PropertyTypeTransformer $propertyTypeTransformer
	 */
	public function __construct(PropertyTypeTransformer $propertyTypeTransformer)
	{
		// parent::__construct();
		$this->propertyTypeTransformer = $propertyTypeTransformer;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index(Request $request, $commercial = null)
	{
		$validator = Validator::make($request->all(), [
			'code' => 'array',
			'code.*' => 'string',
		]);

		if ($validator->fails()) {
			return $this->setValidationErrorJsonResponse($validator->errors());
		}

		$codes = $request->input('code');

		$query = PropertyType::query();

		if ($codes) {
			$query->whereIn('code', $codes);
		}

		if ($commercial) {
			$commercial = ($commercial == 'commercial');
			$query->whereIn('property_type_groupId', function ($query) use ($commercial) {
				$query->select('id')
					->from('property_type_group')
					->where('commercial', $commercial);
			});
		}

		$propertyTypes = $query->orderBy('sort_sequence')->get()->toArray();

		return $this->respond([
			'data' => $this->propertyTypeTransformer->transformCollection($propertyTypes)
		]);
	}


	private function get_property_types($commercial)
	{
		$propertyTypes = \DB::select(
			"SELECT *
			FROM propertytype
			WHERE property_type_groupId IN (
			  SELECT id
			  FROM property_type_group
			  WHERE commercial = ?
			)
			ORDER BY sort_sequence;",
			[$commercial]
		);

		return $this->respond(['data' => $propertyTypes]);
	}
}
