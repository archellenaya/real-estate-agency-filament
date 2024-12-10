<?php

namespace App\Http\Controllers;

/**
 * API5
 */

use App\Models\Consultant;
use Illuminate\Http\Request;
use App\Http\Requests\StoreConsultantRequest;
use App\Niu\Transformers\ConsultantTransformer;
use App\Niu\Transformers\AgentCodeTransformer;
use Exception;
use App\Models\Property;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class ConsultantController
 * @package App\Http\Controllers
 */
class ConsultantController extends ApiController
{
	/**
	 * @var ConsultantTransformer
	 */
	private $consultantTransformer;
	private $agentCodeTransformer;

	/**
	 * level of protection
	 *
	 * @var array
	 */
	protected $apiMethods = [
		'index' => [
			'level' => 10
		],
		'show' => [
			'level' => 10
		],
		'store' => [
			'level' => 20
		],
		'update' => [
			'level' => 20
		],
		'destroy' => [
			'level' => 20
		],
	];

	/**
	 * ConsultantController constructor.
	 *
	 * @param ConsultantTransformer $consultantTransformer
	 */
	public function __construct(ConsultantTransformer $consultantTransformer, AgentCodeTransformer $agentCodeTransformer)
	{
		// parent::__construct();
		$this->consultantTransformer = $consultantTransformer;
		$this->agentCodeTransformer = $agentCodeTransformer;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param null $consultantId
	 *
	 * @return Response
	 */
	public function index(Request $request, $consultantId = null)
	{
		$consultants = $this->getConsultants($consultantId);

		if (isset($consultantId)) {
			$consultants = Consultant::findOrFail($consultantId)->files;
			return $this->respond(
				[
					'data' => $this->consultantTransformer->transformCollection($consultants->all())
				]
			);
		} else {
			$limit = $request->get('limit') ?? '10';
			$consultants = $consultants->paginate($limit);

			return $this->respondWithPagination($consultants, [
				'data' => $this->consultantTransformer->transformCollection($consultants->all())
			]);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreConsultantRequest $request
	 *
	 * @return Response
	 */
	public function store(StoreConsultantRequest $request)
	{
		try {
			$all_request = $request->all();

			$image      = $request->file('image');
			$timestamp  = time();
			$extension  = $image->getClientOriginalExtension();
			$image_name = $all_request['id'] . '_' . $timestamp . '.' . $extension;

			$image->move('uploads', $image_name);

			$all_request['image_file_name_field'] = $image_name;
			$all_request['image_name_field']     = $image->getClientOriginalName();

			$consultant = Consultant::create($all_request);

			return $this->respond($consultant);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$consultant = Consultant::isPublic()
			->where('agent_code', $id)
			->withCount('properties')
			->first();
		if (empty($consultant)) {
			$consultant = Consultant::isPublic()
				->withCount('properties')
				->find($id);
			if (empty($consultant)) {
				return $this->respondNotFound('Consultant does not exist');
			}
		}

		return $this->respond(
			[
				'data' => $this->consultantTransformer->transform($consultant)
			]
		);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 *
	 * @param StoreConsultantRequest $request
	 *
	 * @return Response
	 */
	public function update($id, StoreConsultantRequest $request)
	{
		try {
			$all_request = $request->all();

			$image      = $request->file('image');
			$timestamp  = time();
			$extension  = $image->getClientOriginalExtension();
			$image_name = $all_request['id'] . '_' . $timestamp . '.' . $extension;

			$image->move('uploads', $image_name);

			$all_request['image_file_name_field'] = $image_name;
			$all_request['image_name_field']     = $image->getClientOriginalName();

			$consultant = Consultant::updateOrCreate(['id' => $id], $all_request);

			return $this->respond($consultant);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function destroy($id)
	{
		try {
			$consultant = Consultant::findOrFail($id);
			$consultant->delete();

			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $this->consultantTransformer->transform($consultant)
				]
			);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	/**
	 * @param $consultantId
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	private function getConsultants($consultantId)
	{

		$files = isset($consultantId) ? Consultant::isPublic()->findOrFail($consultantId)->files : Consultant::isPublic()->with('branch')->withCount('properties');;

		return $files;
	}

	public  function getConsultantByCode(Request $request)
	{

		$rules = [
			'aid'       => 'required'
		];

		$messages = [
			'aid' => 'Agent Code required',
		];

		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails()) {
			return $this->setValidationErrorJsonResponse($validator->errors());
		}

		$consultant = Consultant::isPublic()->where('agent_code', $request->aid)->first();
		if (empty($consultant)) {
			$consultant = Consultant::isPublic()->find($request->aid);
			if (empty($consultant)) {
				return $this->respondNotFound('Consultant does not exist');
			}
		}
		return $this->respond(
			[
				'data' => $this->agentCodeTransformer->transform($consultant)
			]
		);
	}
}
