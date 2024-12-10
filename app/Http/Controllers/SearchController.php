<?php namespace App\Http\Controllers;
/**
 * API5
 */
use DB;
use Log;

use DateTime;
use App\Models\Locality;
use Carbon\Carbon;
use App\PropertyType;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use App\Niu\Transformers\PropertyTransformer;

class SearchController extends ApiController {

	/**
	 * @var PropertyTransformer
	 */
	protected $propertyTransformer;

	private $db_select_property_feature_1 = <<<SQL
SELECT property_id
FROM feature_property
WHERE feature_id = :feature_id
GROUP BY property_id
ORDER BY property_id
SQL;

	private $db_select_property_feature_2 = <<<SQL
SELECT property_id
FROM feature_property
WHERE feature_id = :feature_id_1 OR feature_id = :feature_id_2
GROUP BY property_id
HAVING count(property_id) = :counter
ORDER BY property_id
SQL;

	private $db_select_property_feature_3 = <<<SQL
SELECT property_id
FROM feature_property
WHERE feature_id = :feature_id_1 OR feature_id = :feature_id_2 OR feature_id = :feature_id_3
GROUP BY property_id
HAVING count(property_id) = :counter
ORDER BY property_id
SQL;

	private $db_select_property_feature_4 = <<<SQL
SELECT property_id
FROM feature_property
WHERE feature_id = :feature_id_1 OR feature_id = :feature_id_2 OR feature_id = :feature_id_3 OR feature_id = :feature_id_4
GROUP BY property_id
HAVING count(property_id) = :counter
ORDER BY property_id
SQL;

	private $constants = [
		'GARDENS'      => 79,
		'POOLS'        => 10501,
		'HAS_VIEWS'    => 10502,
		'PETS_ALLOWED' => 171
	];

	/**
	 * PropertiesController constructor.
	 *
	 * @param PropertyTransformer $propertyTransformer
	 */
	public function __construct( PropertyTransformer $propertyTransformer ) {
		// parent::__construct();
		$this->propertyTransformer = $propertyTransformer;
	}

	/**
	 * @param                $order
	 * @param Property|null  $properties
	 *
	 * @return Property
	 */
	public function sort( $order, $properties = null ) {
		if ( is_null( $properties ) ) {
			Log::debug("no properies");
			switch ( $order ) {
				case 'ASC':
					return Property::priceASC();
					break;
				case 'DESC':
					return Property::priceDESC();
					break;
				case 'locality':
					return Property::localityASC();
					break;
				case 'type':
					return Property::propertyTypeASC();
					break;
				case 'availability':
					return Property::AvailableFromASC();
					break;
				case 'latest':
					return Property::createdAtDESC();
					break;
				case 'random':
					return Property::random()->weightDESC()->priceDESC();
					break;
				default:
					return Property::weightDESC()->priceASC()->titleASC();
					break;
			}
		} else {
			Log::debug("has properties reorder:".$order);
			switch ( $order ) {
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
				case 'random':
					return $properties->random()->weightDESC()->priceDESC();
					break;
				default:
					return $properties->weightDESC()->priceASC()->titleASC();
					break;
			}
		}
	}

	public function search( Request $request ) {
		$what              = $request->input('what', 'search');
		$limit             = $request->input('limit', 10);
		$sort_order        = $request->get( 'sort' );
		$reference         = $request->get( 'id' );
		$bedrooms          = $request->get( 'bedrooms' );
		$bathrooms         = $request->get( 'bathrooms' );
		$commercial        = $request->get( 'commercial' );
		$toLet             = $request->get( 'toLet' );
		$forSale           = $request->get( 'forSale' );
		$priceFrom         = $request->get( 'priceFrom' );
		$priceTo           = $request->get( 'priceTo' );
		$localities        = $request->get( 'localities' );
		$propertyType      = $request->get( 'propertyType' );
		$property_status    = $request->get( 'property_status' );
		$gardens           = $request->get( 'gardens' );
		$pools             = $request->get( 'pools' );
		$views             = $request->get( 'views' );
		$pets              = $request->get( 'pets' );
		$sqMeters          = $request->get( 'sqmeters' );
		$hotProperty       = $request->get( 'hotProperty' );
		$soleAgent         = $request->get( 'soleAgent' );
		$newProperty       = $request->get( 'newProperty' );
		$featuredProperty  = $request->get( 'featuredProperty' );
		$areaFrom          = $request->get( 'areaFrom' );
		$areaTo            = $request->get( 'areaTo' );
		$dateOnMarket      = $request->get( 'dateOnMarket' );
		$availableFrom     = $request->get( 'availableFrom' );
		$weightFrom        = $request->get( 'weightFrom' );
		$weightTo          = $request->get( 'weightTo' );
		$is_managed_property = $request->get( 'is_managed_property' );
		$dateOffMarket     = $request->get( 'dateOffMarket' );

		// $re_sort = null;
		
		// if($propertyType && in_array(3, $propertyType) && in_array(13, $propertyType)==false)
		// {
		// 	array_push($propertyType, 13);
		// 	$re_sort = 'asc';
		// }

		// if($propertyType && in_array(3, $propertyType)==false && in_array(13, $propertyType)){
		// 	array_push($propertyType, 3);
		// 	$re_sort = 'desc';
		// }
	
		if ( $what === 'search' ) {
		
			$search_result = $this->do_search(
				$reference, $bedrooms, $bathrooms, $commercial, $toLet, $forSale, $priceFrom, $priceTo, $localities,
				$propertyType, $property_status, $hotProperty, $soleAgent, $newProperty, $featuredProperty, $gardens,
				$pools, $views, $pets, $sqMeters, $areaFrom, $areaTo, $dateOnMarket, $availableFrom, $weightFrom,
				$weightTo, $is_managed_property, null, $dateOffMarket
			);
			$data = $search_result->paginate( $limit );
			// return $data;
			$this->sort( $sort_order, $search_result );
			
			// if($propertyType && $re_sort!=null) {
			// 	if($re_sort=='asc')
			// 		$search_result->propertyTypeASC();
			// 	else	
			// 		$search_result->propertyTypeDESC();
			// }
			
			$data = $search_result->paginate( $limit );
			// return $data;
			return $this->respondWithPagination( $data, [
				'data' => $this->propertyTransformer->transformCollection( $data->all() )
			] );
		} else {
			// Log::debug("do_fallback_search");
			$fallback_result = $this->do_fallback_search(
				null, $reference, $bedrooms, $bathrooms, $commercial, $toLet, $forSale, $priceFrom, $priceTo,
				$localities, $propertyType, $property_status, $hotProperty, $soleAgent, $newProperty, $featuredProperty,
				$gardens, $pools, $views, $pets, $sqMeters, $areaFrom, $areaTo, $dateOnMarket, $availableFrom,
				$weightFrom, $weightTo, $is_managed_property
			);

			$this->sort( $sort_order, $fallback_result );
			
			// if($propertyType && $re_sort!=null) {
			// 	if($re_sort=='asc')
			// 		$fallback_result->propertyTypeASC();
			// 	else	
			// 		$fallback_result->propertyTypeDESC();
			// }
			
			$data = $fallback_result->paginate( $limit );

			return $this->respondWithPagination( $data, [
				'data' => $this->propertyTransformer->transformCollection( $data->all() )
			]);
		}
	}

	/**
	 * @param             $reference
	 * @param             $bedrooms
	 * @param             $bathrooms
	 * @param             $commercial
	 * @param             $toLet
	 * @param             $forSale
	 * @param             $priceFrom
	 * @param             $priceTo
	 * @param             $localities
	 * @param             $propertyType
	 * @param             $property_status
	 * @param             $hotProperty
	 * @param             $soleAgent
	 * @param             $newProperty
	 * @param             $featuredProperty
	 * @param             $gardens
	 * @param             $pools
	 * @param             $views
	 * @param             $pets
	 * @param             $sqMeters
	 * @param             $areaFrom
	 * @param             $areaTo
	 * @param             $dateOnMarket
	 * @param             $availableFrom
	 * @param             $weightFrom
	 * @param             $weightTo
	 * @param             $is_managed_property
	 * @param null        $excluded
	 * @param null|string $offMarketFrom
	 *
	 * @return Property|\Illuminate\Database\Query\Builder
	 */
	private function do_search(
		$reference, $bedrooms, $bathrooms, $commercial, $toLet, $forSale, $priceFrom, $priceTo, $localities,
		$propertyType, $property_status, $hotProperty, $soleAgent, $newProperty, $featuredProperty,
		$gardens, $pools, $views, $pets, $sqMeters, $areaFrom, $areaTo, $dateOnMarket, $availableFrom, $weightFrom, $weightTo,
		$is_managed_property, $excluded = null, $offMarketFrom = null
	) {
		/**
		 * @var $properties Builder
		 */
		if ( is_null( $offMarketFrom ) || DateTime::createFromFormat( 'Y-m-d H:i:s', $offMarketFrom ) === false ) {
			$properties = Property::where( 'market_status_field', 'OnMarket' );
		} else {
			//@todo-omar: Remove duplication of code with below
			$properties = Property::whereNotNull( 'created_at' )
			                      ->where( function ( $query ) use ( $offMarketFrom ) {
				                      /** @var $query Builder */
				                      $query->where( 'market_status_field', 'OnMarket' )
				                            ->orWhere( function ( $sub_query ) use ( $offMarketFrom ) {
					                            /** @var $sub_query Builder */
					                            $sub_query->where( 'market_status_field', 'OffMarket' );
					                            $sub_query->where( 'date_off_market_field', '>=', $offMarketFrom );
				                            });
			                      });
		}

		$properties->where( 'market_type_field', '!=', 'ShortLet' );
		$properties->where( 'show_in_searches', '=', 1 );

		if($localities_to_skip = env('PROPERTIES_IN_LOCALITES_TO_SKIP',null)){
			$localities_to_skip = explode(',',$localities_to_skip);
			$properties->whereNotIn('locality_id_field',$localities_to_skip);
		}

		if ( ! is_null( $excluded ) && ! empty( $excluded ) ) {
			$properties->whereNotIn( 'id', $excluded );
		}
		
		$properties->where( function ( $query ) use ( $offMarketFrom ) {
			//@todo-omar: Remove duplication of code with above
			/** @var $query Builder */
			if ( is_null( $offMarketFrom ) || DateTime::createFromFormat( 'Y-m-d H:i:s', $offMarketFrom ) === false ) {
				$query->where( 'expiry_date_time', '>=', Carbon::now() )
				      ->orWhere( 'market_status_field', 'OnMarket' );
			} else {
				$query->where( 'expiry_date_time', '>=', Carbon::now() )
				      ->orWhere( 'market_status_field', 'OnMarket' )
				      ->orWhere( function ( $sub_query ) use ( $offMarketFrom ) {
					      /** @var $sub_query Builder */
					      $sub_query->where( 'market_status_field', 'OffMarket' );
					      $sub_query->where( 'date_off_market_field', '>=', $offMarketFrom );
				      } );
			}
		} );
		if ( $bedrooms ) {
			$properties->where( 'bedrooms_field', '>=', $bedrooms );
		}
		if ( $bathrooms ) {
			$properties->where( 'bathrooms_field', '>=', $bathrooms );
		}
		if ( is_numeric($commercial) && $commercial !== null ) {
			$properties->where( 'commercial_field', $commercial );
		}
		if ( $toLet && $reference == '' ) {
			$properties->where( 'market_type_field', 'LongLet' );
		}
		if ( $forSale && $reference == '' ) {
			$properties->where( 'market_type_field', 'ForSale' );
		}
		if ( $priceFrom ) {
			$properties->where( 'price_field', '>=', $priceFrom );
		}
		if ( $priceTo ) {
			$properties->where( 'price_field', '<=', $priceTo );
		}
		if ( $localities ) {
			$localities = DB::table('locality')->whereIn('id', $localities)->orWhereIn('parent_locality_id', $localities)->pluck('id');
			$properties->whereIn( 'locality_id_field', $localities );
		}
		if ( $propertyType ) {
			$properties->whereIn( 'property_type_id_field', $propertyType );
		} else {
			//If Commercial is not set Hide both Commercial & Residential Garages
			$properties->whereNotIn( 'property_type_id_field', [ 18, 20000039 ] );
		}
		if ( $property_status ) {
			$properties->whereIn( 'property_status_id_field', $property_status );
		}
		if ( $hotProperty ) {
			$properties->where( 'is_hot_property_field', $hotProperty );
		}
		if ( $soleAgent ) {
			$properties->where( 'sole_agents_field', $soleAgent );
		}
		if ( $newProperty ) {
			$properties->where( 'date_on_market_field', '>=', Carbon::now()->subMonth() );
		}
		if ( $featuredProperty ) {
			$properties->where( 'is_property_of_the_month_field', $featuredProperty );
		}
		if ( $areaFrom ) {
			$properties->where( 'area_field', '>=', $areaFrom );
		}
		if ( $areaTo ) {
			$properties->where( 'area_field', '<=', $areaTo );
		}
		if ( $availableFrom ) {
			$properties->where( 'date_available_field', '<=', Carbon::parse( $availableFrom ) );
		}
		if ( $dateOnMarket ) {
			$properties->where( 'date_on_market_field', '>=', Carbon::parse( $dateOnMarket ) );
		}
		if ( $weightFrom ) {
			$properties->where( 'weight_field', '>=', (int) $weightFrom );
		}
		if ( $weightTo ) {
			$properties->where( 'weight_field', '<=', (int) $weightTo );
		}
		if ( $is_managed_property ) {
			$properties->where( 'is_managed_property', $is_managed_property );
		}

		$features_properties  = null;
		$feature_select_array = $features_search = [];
		$features_counter     = 0;
		if ( $gardens ) {
			$features_search[] = $this->constants['GARDENS'];
			$features_counter ++;
		}
		if ( $pools ) {
			$features_search[] = $this->constants['POOLS'];
			$features_counter ++;
		}
		if ( $views ) {
			$features_search[] = $this->constants['HAS_VIEWS'];
			$features_counter ++;
		}
		if ( $pets ) {
			$features_search[] = $this->constants['PETS_ALLOWED'];
			$features_counter ++;
		}

		if ( ! empty ( $features_search ) ) {
			if ( $features_counter == 1 ) {
				$feature_select_array['feature_id'] = $features_search[0];
				$features_properties                = DB::select(
					$this->db_select_property_feature_1,
					$feature_select_array
				);
			} elseif ( $features_counter == 2 ) {
				$feature_select_array['feature_id_1'] = $features_search[0];
				$feature_select_array['feature_id_2'] = $features_search[1];
				$feature_select_array['counter']      = 2;
				$features_properties                  = DB::select(
					$this->db_select_property_feature_2,
					$feature_select_array
				);
			} elseif ( $features_counter == 3 ) {
				$feature_select_array['feature_id_1'] = $features_search[0];
				$feature_select_array['feature_id_2'] = $features_search[1];
				$feature_select_array['feature_id_3'] = $features_search[2];
				$feature_select_array['counter']      = 3;
				$features_properties                  = DB::select(
					$this->db_select_property_feature_3,
					$feature_select_array
				);
			} elseif ( $features_counter == 4 ) {
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

		if ( ! is_null( $features_properties ) ) {
			$properties_ids = [];
			foreach ( $features_properties as $value ) {
				$properties_ids[] = $value->property_id;
			}

			$properties->whereIn( 'id', $properties_ids );
		}

		if ( $sqMeters ) {
			$properties->where( 'area_field', '>=', $sqMeters );
		}
	
		return $properties;
	}


	private function do_fallback_search(
		$excluded, $reference, $bedrooms, $bathrooms, $commercial, $toLet, $forSale, $priceFrom, $priceTo, $localities,
		$propertyType, $property_status, $hotProperty, $soleAgent, $newProperty, $featuredProperty,
		$gardens, $pools, $views, $pets, $sqMeters, $areaFrom, $areaTo, $dateOnMarket, $availableFrom, $weightFrom, $weightTo,
		$is_managed_property
	) {
		/**
		 * if no properties are found with this criteria expand to:
		 * - increase decrease in price by 15%
		 * - property type
		 * - region
		 */

		if ( $propertyType ) {
			$property_type_groupId = PropertyType::whereIn( 'id', $propertyType )->first()->property_type_groupId;
			$propertyTypes       = PropertyType::where( 'property_type_groupId', $property_type_groupId )->get()->toArray();
			$propertyType        = [];
			foreach ( $propertyTypes as $pt ) {
				$propertyType[] = $pt['id'];
			}
		}

		if ( $localities ) {
			$zone = null;
			foreach ( $localities as $locality ) {
				$zone = Locality::whereId( $locality )->first()->zoneId;
			}

			$localities       = [];
			$localitiesInZone = Locality::where( 'zoneId', $zone )->get()->toArray();
			foreach ( $localitiesInZone as $locality ) {
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
			$priceFrom - ( $priceFrom * 0.15 ),
			$priceTo + ( $priceTo * 0.15 ),
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
			null,
			null,
			$is_managed_property,
			$excluded
		);
	}

	public function getLatest( Request $request ) {
		$limit        = $request->input('limit', 10);
		$dateOnMarket = $request->get( 'dateOnMarket' );
		$updatedAt    = $request->get( 'updatedAt' );

		/**
		 * @var $properties Builder
		 */
		$properties = Property::where( 'market_status_field', 'OnMarket' );
		if ( $dateOnMarket ) {
			$properties->where( 'date_on_market_field', '>=', Carbon::parse( $dateOnMarket ) );
		}
		if ( $updatedAt ) {
			$properties->where( 'updated_at', '>=', Carbon::parse( $updatedAt ) );
		}
		$data = $properties->paginate( $limit );

		return $this->respondWithPagination( $data, [
			'data' => $this->propertyTransformer->transformCollection( $data->all() )
		] );
	}
	
	public function quickSearch(Request $request){
		$limit        = $request->input('limit', 9);

		if(empty($request->get( 'search')) === true ){
			return abort( 422,'No search');
		}

		$searchQuery = $request->get( 'search' );
		$results = Property::where('property_ref_field','LIKE',"%$searchQuery%")
			->orderBy('weight_field', 'title_field') 
			->paginate( $limit );
		return $results;
		// return $this->respondWithPagination( $results, [
		// 	'data' => $this->propertyTransformer->transformCollection( $results->all() )
		// ]);
	}
}
