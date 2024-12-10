<?php

namespace App\Http\Controllers;

/**
 * API5
 */

use DB;
use Exception;
use App\Models\Locality;
use Carbon\Carbon;
use App\Models\Consultant;
use App\PropertyType;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StorePropertyRequest;
use App\Niu\Transformers\PropertyTransformer;

/**
 * Class PropertiesController
 *
 * @package App\Http\Controllers
 */
class PropertiesController extends ApiController
{

	/**
	 * @var PropertyTransformer
	 */
	protected $propertyTransformer;
	protected $expiration_time = 2628288;
	/**
	 * level of protection
	 *
	 * @var array
	 */
	protected $apiMethods = [
		'index'          => [
			'level' => 10
		],
		'getPropertiesByRefs' => [
			'level' => 10
		],
		'show'           => [
			'level' => 10
		],
		'store'          => [
			'level' => 20
		],
		'update'         => [
			'level' => 20
		],
		'destroy'        => [
			'level' => 20
		],
		'listProperties' => [
			'level' => 10
		]
	];

	/**
	 * PropertiesController constructor.
	 *
	 * @param PropertyTransformer $propertyTransformer
	 */
	public function __construct(PropertyTransformer $propertyTransformer)
	{
		// parent::__construct();

		$this->propertyTransformer = $propertyTransformer;
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
		$sort = $request->input('sort') ?: null;

		$properties = $this->getProperties($request, $consultantId, $sort);

		return $this->respondWithPagination($properties, [
			'data' => $this->propertyTransformer->transformCollection($properties->all())
		]);
	}

	public function getPropertiesByRefs(Request $request)
	{
		$refs = $request->get('refs');
		$aid  = $request->get('aid');

		if (empty($refs)) {
			return [];
		}

		if (is_string($refs))
			$refs =  explode(',', $refs);

		$getOnlyIds = (bool)((int)$request->get('getIDsOnly', 0) === 1);

		$cache_key = Utilities::createCacheKey('property', implode("-", $refs), $aid);
		$tags = [Utilities::slugify(config("app.name")), $cache_key];
		$tags = array_merge($tags, $refs);
		// if(config("cache.default") == "memcached") { 
		// 	return Cache::tags($tags)->remember($cache_key, $this->expiration_time, function() use($refs, $getOnlyIds, $aid) {
		// 		return $this->getProperty($refs, $getOnlyIds, $aid);
		// 	});
		// } else { 
		// return Cache::remember($cache_key, $this->expiration_time, function() use($refs, $getOnlyIds){
		return $this->getProperty($refs, $getOnlyIds, $aid);
		// });
		// }
	}

	public function getProperty($refs, $getOnlyIds, $aid)
	{
		$properties = Property::whereIn('property_ref_field', $refs);

		if ($getOnlyIds === false) {
			$propertiesTransformed = $this->propertyTransformer->transformCollection($properties->get()->all());


			if (isset($aid)) { //agent_code_id
				$consultant = Consultant::where('agent_code', $aid)->first();
				if (empty($consultant)) {
					$consultant = Consultant::find($aid);
				}
				if (!empty($consultant)) {

					$consultant_aid = [
						'id'             => $consultant->id,
						'old_id'    	 => $consultant->old_id,
						'fullNameField'   => $consultant->full_name_field,
						'imageFilenameField' => $consultant->image_file_name_field,
						'imageNameField'       => $consultant->image_name_field,
						'branchId'      => $consultant->branch_id_field,
						'descriptionField'    => $consultant->description_field,
						'designationField'    => $consultant->designation_field,
						'contactNumberField' => $consultant->contact_number_field,
						'officePhoneField'   => $consultant->office_phone_field,
						'emailField'          => $consultant->email_field,
						'isAvailable'    => $consultant->is_available,
						'sourcePhotoUrl'  => $consultant->orig_consultant_image_src,
						'listingCounts' => $consultant->properties()->count()
					];
				}
			}

			$new_propertiesTransformed = [];
			foreach ($propertiesTransformed as $property) {
				$formatted_features = [];
				foreach ($property['features'] as $feature) {
					$formatted_features[$feature["id"]] = $feature;
				}
				$property['formatted_features'] = $formatted_features;
				if (!empty($consultant_aid)) {
					$property['consultant'] = $consultant_aid;
				}
				$new_propertiesTransformed[] = $property;
			}

			return $new_propertiesTransformed;
		}

		return collect($properties->get())->map(function ($property) {
			return strtoupper($property->id ?? null);
		});
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listProperties(Request $request)
	{
		$properties = $request->get('properties');

		return $this->get_list_of_properties($request, $properties);
	}

	private function get_list_of_properties(Request $request, $properties, $order = null)
	{
		$limit = $request->input('limit', 10);

		$properties = $this->sort($order, Property::isPublic()->whereIn('id', $properties))->paginate($limit);

		return $this->respondWithPagination($properties, [
			'data' => $this->propertyTransformer->transformCollection($properties->all()),
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StorePropertyRequest $request
	 *
	 * @return Response
	 */
	public function store(StorePropertyRequest $request)
	{
		try {
			$property = Property::create($request->all());

			return $this->respond($property);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function show($id)
	{
		/**
		 * @var $property Property
		 */
		$property = Property::isPublic()->find($id);

		if (! $property) {
			return $this->respondNotFound('Property does not exist');
		}

		if (($property->market_status_field == 'OffMarket') && ($property->expiry_date_time < Carbon::now())) {
			return $this->respondNotFound('Property Sold');
		}

		return $this->respond(
			[
				'data' => $this->propertyTransformer->transform($property)
			]
		);
	}

	public function getPropertyByRef(Request $request)
	{
		$ref = $request->get('refs');
		$aid  = $request->get('aid');

		if (empty($ref)) {
			return [];
		}

		// return Cache::remember("property-" . $ref, $this->expiration_time, function () use ($ref) {
		$property_query = Property::where('property_ref_field', $ref);

		$property_query->where(function ($query) {
			$query->whereNotIn('market_status_field', ["Sold", "Discreet Offline"])->orWhereNull("market_status_field");
		})->where('status', 1);

		if (! $property_query) {
			return $this->respondNotFound('Property does not exist');
		}

		if (isset($aid)) { //agent_code_id
			$consultant = Consultant::where('agent_code', $aid)->first();
			if (empty($consultant)) {
				$consultant = Consultant::find($aid);
			}
			if (!empty($consultant)) {

				$consultant_aid = [
					'id'             => $consultant->id ?? null,
					'old_id'         => $consultant->old_id ?? null,
					'fullNameField'   => $consultant->full_name_field ?? null,
					'imageFilenameField' => $consultant->image_file_name_field ?? 'default.webp',
					'imageNameField'       => $consultant->image_name_field ?? 'default.webp',
					'branchId'      => $consultant->branch_id_field ?? null,
					'branch_name' => $consultant->getBranchName() ?? null,
					'descriptionField'    => $consultant->description_field ?? null,
					'designationField'    => $consultant->designation_field ?? null,
					'whatsappNumberField'    => $consultant->whatsapp_number_field ?? null,
					'contactNumberField' => $consultant->contact_number_field ?? null,
					'officePhoneField'   => $consultant->office_phone_field ?? null,
					'emailField'          => $consultant->email_field ?? null,
					'isAvailable'    => $consultant->is_available ?? null,
					'sourcePhotoUrl'  => $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
					'image_status_field' => $consultant->image_status_field ?? null,
					'url_field' => $consultant->url_field ?? $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
					'listingCounts' => $consultant->properties()->count()
				];
			}
		}

		$propertyTransformed = $this->propertyTransformer->transformCollection($property_query->get()->all());
		$new_propertyTransformed = [];
		foreach ($propertyTransformed as $property) {

			$formatted_features = [];
			foreach ($property['features'] as $feature) {
				$formatted_features[$feature["id"]] = $feature;
			}
			$property['formatted_features'] = $formatted_features;
			if (!empty($consultant_aid)) {
				$property['consultant'] = $consultant_aid;
			}
			$new_propertyTransformed[] = $property;
		}
		return $new_propertyTransformed;
		// });
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param int                  $id
	 *
	 * @param StorePropertyRequest $request
	 *
	 * @return Response
	 */
	public function update($id, StorePropertyRequest $request)
	{
		try {
			$property = Property::updateOrCreate(['id' => $id], $request->all());

			return $this->respond($property);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function destroy($id)
	{
		try {

			//Find the property we need to delete
			$property = Property::findOrFail($id);


			//We can now delete the property
			$property->delete();

			//Return the response after deletion
			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $this->propertyTransformer->transform($property)
				]
			);
		} catch (Exception $e) {
			return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
		}
	}

	public function search(Request $request)
	{
		$limit            = $request->input('limit', 10);
		$sort_order       = $request->get('sort');
		$reference        = $request->get('id');
		$bedrooms         = $request->get('bedrooms');
		$bathrooms        = $request->get('bathrooms');
		$commercial       = $request->get('commercial');
		$toLet            = $request->get('toLet');
		$forSale          = $request->get('forSale');
		$priceFrom        = $request->get('priceFrom');
		$priceTo          = $request->get('priceTo');
		$localities       = $request->get('localities');
		$propertyType     = $request->get('propertyType');
		$property_status   = $request->get('property_status');
		$gardens          = $request->get('gardens');
		$pools            = $request->get('pools');
		$views            = $request->get('views');
		$pets             = $request->get('pets');
		$sqMeters         = $request->get('sqmeters');
		$hotProperty      = $request->get('hotProperty');
		$soleAgent        = $request->get('soleAgent');
		$newProperty      = $request->get('newProperty');
		$featuredProperty = $request->get('featuredProperty');
		$areaFrom         = $request->get('areaFrom');
		$areaTo           = $request->get('areaTo');
		$dateOnMarket     = $request->get('dateOnMarket');
		$availableFrom    = $request->get('availableFrom');


		$search_result = $this->do_search(
			$reference,
			$bedrooms,
			$bathrooms,
			$commercial,
			$toLet,
			$forSale,
			$priceFrom,
			$priceTo,
			$localities,
			$propertyType,
			$property_status,
			$hotProperty,
			$soleAgent,
			$newProperty,
			$featuredProperty,
			$gardens,
			$pools,
			$views,
			$pets,
			$sqMeters,
			$areaFrom,
			$areaTo,
			$dateOnMarket,
			$availableFrom
		);

		$properties = $this->sort($sort_order, Property::whereIn('id', $search_result))->paginate($limit);


		$out           = [];
		$out['search'] = $this->getWithPagination($properties, [
			'data' => $this->propertyTransformer->transformCollection($properties->all()),
		]);

		if (count($search_result) <= $limit) {
			$fallback_search_result = $this->do_fallback_search(
				$search_result,
				$reference,
				$bedrooms,
				$bathrooms,
				$commercial,
				$toLet,
				$forSale,
				$priceFrom,
				$priceTo,
				$localities,
				$propertyType,
				$property_status,
				$hotProperty,
				$soleAgent,
				$newProperty,
				$featuredProperty,
				$gardens,
				$pools,
				$views,
				$pets,
				$sqMeters,
				$areaFrom,
				$areaTo,
				$dateOnMarket,
				$availableFrom
			);

			$properties_fallback_result = $this->sort($sort_order, Property::whereIn('id', $fallback_search_result))->paginate($limit);
			$out['fallback']            = $this->getWithPagination($properties_fallback_result, [
				'data' => $this->propertyTransformer->transformCollection($properties_fallback_result->all()),
			]);
		}

		return $this->respond($out);
	}

	/**
	 * @param      $consultantId
	 * @param null $order
	 *
	 * @return mixed
	 */
	public function getProperties(Request $request, $consultantId, $order = null)
	{
		$limit = $request->input('limit', 10);

		if ($consultantId) {
			$properties = $this->sort($order, Property::isPublic()->where('market_status_field', '=', 'OnMarket')->whereConsultantId($consultantId))
				->with([
					'property_type',
					'locality',
					'property_status',
					'consultant'  => function ($query) {
						$query->withCount('properties'); 
					},
					'features',
					'files',
					'region',
					'project'
				]);
		} else {
			$properties = $this->sort($order);
		}

		return $properties->paginate($limit);
	}

	/**
	 * @param                $order
	 * @param Property|null  $properties
	 *
	 * @return Property
	 */
	public function sort($order, $properties = null)
	{
		if (is_null($properties)) {
			$query = Property::query();
			switch ($order) {
				case 'ASC':
					$query = $query->isPublic()->priceASC();
					break;
				case 'DESC':
					$query = $query->isPublic()->priceDESC();
					break;
				case 'locality':
					$query = $query->isPublic()->localityASC();
					break;
				case 'type':
					$query = $query->isPublic()->propertyTypeASC();
					break;
				case 'availability':
					$query = $query->isPublic()->AvailableFromASC();
					break;
				case 'latest':
					$query = $query->isPublic()->createdAtDESC();
					break;
				default:
					$query = $query->isPublic()->weightDESC()->priceASC()->titleASC();
					break;
			}
			return $query->with([
				'property_type',
				'locality',
				'property_status',
				'consultant'  => function ($query) {
					$query->withCount('properties'); 
				},
				'features',
				'files',
				'region',
				'project'
			]);
		} else {
			switch ($order) {
				case 'ASC':
					return $properties->priceASC();
					break;
				case 'DESC':
					return $properties->priceDESC();
					break;
				case 'locality':
					return $properties->localityASC();
					break;
				case 'type':
					return $properties->propertyTypeASC();
					break;
				case 'availability':
					return $properties->AvailableFromASC();
					break;
				case 'latest':
					return $properties->createdAtDESC();
					break;
				default:
					return $properties->weightDESC()->priceASC()->titleASC();
					break;
			}
		}
	}

	/**
	 * @param      $reference
	 * @param      $bedrooms
	 * @param      $bathrooms
	 * @param      $commercial
	 * @param      $toLet
	 * @param      $forSale
	 * @param      $priceFrom
	 * @param      $priceTo
	 * @param      $localities
	 * @param      $propertyType
	 * @param      $property_status
	 * @param      $hotProperty
	 * @param      $soleAgent
	 * @param      $newProperty
	 * @param      $featuredProperty
	 * @param      $gardens
	 * @param      $pools
	 * @param      $views
	 * @param      $sqMeters
	 * @param      $areaFrom
	 * @param      $areaTo
	 * @param      $dateOnMarket
	 * @param      $availableFrom
	 * @param null $excluded
	 *
	 * @return array
	 */
	private function do_search(
		$reference,
		$bedrooms,
		$bathrooms,
		$commercial,
		$toLet,
		$forSale,
		$priceFrom,
		$priceTo,
		$localities,
		$propertyType,
		$property_status,
		$hotProperty,
		$soleAgent,
		$newProperty,
		$featuredProperty,
		$gardens,
		$pools,
		$views,
		$pets,
		$sqMeters,
		$areaFrom,
		$areaTo,
		$dateOnMarket,
		$availableFrom,
		$excluded = null
	) {
		$results = DB::table('properties');

		$results->where(function ($query) {
			/** @var $query Builder */
			$query->where('expiry_date_time', '>=', Carbon::now())->orWhere('market_status_field', 'OnMarket');
		});

		if (! is_null($excluded) && ! empty($excluded)) {
			$results->whereNotIn('id', $excluded);
		}

		if ($reference) {
			$results->where('id', 'like', '%' . $reference . '%');
		}
		if ($bedrooms) {
			$results->where('bedrooms_field', '>=', $bedrooms);
		}
		if ($bathrooms) {
			$results->where('bathrooms_field', '>=', $bathrooms);
		}
		if ($commercial) {
			$results->where('commercial_field', $commercial);
		}
		if ($toLet && $reference == '') {
			//Short Lets Should Not Be Listed
			//$results->whereIn( 'market_type_field', [ 'LongLet', 'ShortLet' ] );
			$results->where('market_type_field', 'LongLet');
		}
		$results->where('market_type_field', '!=', 'ShortLet');
		if ($forSale && $reference == '') {
			$results->where('market_type_field', 'ForSale');
		}
		if ($priceFrom) {
			$results->where('price_field', '>=', $priceFrom);
		}
		if ($priceTo) {
			$results->where('price_field', '<=', $priceTo);
		}
		if ($localities) {
			$temp_localities = DB::table('locality');
			$temp_localities->whereIn('parent_locality_id', $localities);
			$new_localities = $temp_localities->lists('id');

			$localities = array_merge($localities, $new_localities);

			$results->whereIn('locality_id_field', $localities);
		}
		if ($propertyType) {
			$results->whereIn('property_type_id_field', $propertyType);
		}
		if ($property_status) {
			$results->whereIn('property_status_id_field', $property_status);
		}
		if ($hotProperty) {
			$results->where('is_hot_property_field', $hotProperty);
		}
		if ($soleAgent) {
			$results->where('sole_agents_field', $soleAgent);
		}
		if ($newProperty) {
			$results->where('date_on_market_field', '>=', Carbon::now()->subMonth());
		}
		if ($featuredProperty) {
			$results->where('is_property_of_the_month_field', $featuredProperty);
		}
		if ($areaFrom) {
			$results->where('area_field', '>=', $areaFrom);
		}
		if ($areaTo) {
			$results->where('area_field', '<=', $areaTo);
		}

		if ($availableFrom) {
			$results->where('date_available_field', '<=', Carbon::parse($availableFrom));
		}
		if ($dateOnMarket) {
			$results->where('date_on_market_field', '>=', Carbon::parse($dateOnMarket));
		}

		$features_properties  = null;
		$feature_select_array = $features_search = [];
		$features_counter     = 0;
		if ($gardens) {
			$features_search[] = $this->constants['GARDENS'];
			$features_counter++;
		}
		if ($pools) {
			$features_search[] = $this->constants['POOLS'];
			$features_counter++;
		}
		if ($views) {
			$features_search[] = $this->constants['HAS_VIEWS'];
			$features_counter++;
		}
		if ($pets) {
			$features_search[] = $this->constants['PETS_ALLOWED'];
			$features_counter++;
		}

		if (! empty($features_search)) {
			if ($features_counter == 1) {
				$feature_select_array['feature_id'] = $features_search[0];
				$features_properties                = DB::select(
					$this->db_select_property_feature_1,
					$feature_select_array
				);
			} elseif ($features_counter == 2) {
				$feature_select_array['feature_id_1'] = $features_search[0];
				$feature_select_array['feature_id_2'] = $features_search[1];
				$feature_select_array['counter']      = 2;
				$features_properties                  = DB::select(
					$this->db_select_property_feature_2,
					$feature_select_array
				);
			} elseif ($features_counter == 3) {
				$feature_select_array['feature_id_1'] = $features_search[0];
				$feature_select_array['feature_id_2'] = $features_search[1];
				$feature_select_array['feature_id_3'] = $features_search[2];
				$feature_select_array['counter']      = 3;
				$features_properties                  = DB::select(
					$this->db_select_property_feature_3,
					$feature_select_array
				);
			} elseif ($features_counter == 4) {
				$feature_select_array['feature_id_1'] = $features_search[0];
				$feature_select_array['feature_id_2'] = $features_search[1];
				$feature_select_array['feature_id_3'] = $features_search[2];
				$feature_select_array['feature_id_4'] = $features_search[3];
				$feature_select_array['counter']      = 4;
				$features_properties                  = DB::select(
					$this->db_select_property_feature_4,
					$feature_select_array
				);
			}
		}

		if (! is_null($features_properties)) {
			$properties_ids = [];
			foreach ($features_properties as $value) {
				$properties_ids[] = $value->property_id;
			}

			$results->whereIn('id', $properties_ids);
		}

		if ($sqMeters) {
			$results->where('area_field', '>=', $sqMeters);
		}

		$out = $results->lists('id');

		//		dd($results->toSql());
		//		dd($results->getBindings());

		return $out;
	}

	private function do_fallback_search(
		$excluded,
		$reference,
		$bedrooms,
		$bathrooms,
		$commercial,
		$toLet,
		$forSale,
		$priceFrom,
		$priceTo,
		$localities,
		$propertyType,
		$property_status,
		$hotProperty,
		$soleAgent,
		$newProperty,
		$featuredProperty,
		$gardens,
		$pools,
		$views,
		$pets,
		$sqMeters,
		$areaFrom,
		$areaTo,
		$dateOnMarket,
		$availableFrom
	) {
		/**
		 * if no properties are found with this criteria expand to:
		 * - increase decrease in price by 15%
		 * - property type
		 * - region
		 */

		if ($propertyType) {
			if (isset($_GET['testing'])) {
				//                var_dump( \property_type_groupId );
				dd(PropertyType::whereId($propertyType)->first()->property_type_groupId);
			}
			$property_type_groupId = PropertyType::whereId($propertyType)->first()->property_type_groupId;
			if (isset($_GET['testing'])) {
				dd($property_type_groupId);
			}
			$propertyTypes = PropertyType::where('property_type_groupId', $property_type_groupId)->get()->toArray();
			$propertyType  = [];
			foreach ($propertyTypes as $pt) {
				$propertyType[] = $pt['id'];
			}
		}

		if ($localities) {
			$zone = null;
			foreach ($localities as $locality) {
				$zone = Locality::whereId($locality)->first()->zoneId;
			}

			$localities       = [];
			$localitiesInZone = Locality::where('zoneId', $zone)->get()->toArray();
			foreach ($localitiesInZone as $locality) {
				$localities[] = $locality['id'];
			}
		}

		return $this->do_search(
			$reference,
			$bedrooms,
			$bathrooms,
			$commercial,
			$toLet,
			$forSale,
			$priceFrom - ($priceFrom * 0.15),
			$priceTo   + ($priceTo * 0.15),
			$localities,
			$propertyType,
			$property_status,
			false,
			false,
			false,
			false,
			$gardens,
			$pools,
			$views,
			$pets,
			$sqMeters,
			$areaFrom,
			$areaTo,
			$dateOnMarket,
			$availableFrom,
			$excluded
		);
	}
}
