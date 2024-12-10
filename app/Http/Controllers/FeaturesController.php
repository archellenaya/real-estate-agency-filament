<?php namespace App\Http\Controllers;
/**
 * API5
 */
use Exception;
use App\Feature;
use App\Http\Requests;
use Illuminate\Http\Response;
use App\Models\Property;
use App\Http\Requests\StoreFeatureRequest;
use App\Niu\Transformers\FeatureTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * Class FeaturesController
 * @package App\Http\Controllers
 */
class FeaturesController extends ApiController {

	/**
	 * @var FeatureTransformer
	 */
	protected $featureTransformer;

	/**
	 * level of protection
	 *
	 * @var array
	 */
	protected $apiMethods = [
		'index'   => [
			'keyAuthentication' => false
		],
		'show'    => [
			'level' => 10
		],
		'store'   => [
			'level' => 20
		],
		'update'  => [
			'level' => 20
		],
		'destroy' => [
			'level' => 20
		],
	];

	/**
	 * FeaturesController constructor.
	 *
	 * @param FeatureTransformer $featureTransformer
	 */
	public function __construct( FeatureTransformer $featureTransformer ) {
		// parent::__construct();
		$this->featureTransformer = $featureTransformer;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param null $propertyID
	 *
	 * @return Response
	 * @throws ModelNotFoundException
	 */
	public function index( $propertyID = null ) {
		$features = $this->getFeatures( $propertyID );
		
		return $this->respond(
			[
				'data' => $this->featureTransformer->transformCollection( $features->all() )
			]
		);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param null $propertyID
	 *
	 * @param StoreFeatureRequest $request
	 *
	 * @return Response
	 */
	public function store( $propertyID = null, StoreFeatureRequest $request ) {
		try {
			$property = Property::findOrFail( $propertyID );

			$property->features()->attach(
				$request->input( 'features' ),
				[
					'feature_value' => $request->input( 'feature_value' )
				]
			);

			$features = $this->getFeatures( $propertyID );

			return $this->respond(
				[
					'message' => 'Features added successfully',
					'data'    => $this->featureTransformer->transformCollection( $features->all() )
				]
			);
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param null $propertyID
	 * @param $featureID
	 *
	 * @return Response
	 */
	public function destroy( $propertyID = null, $featureID ) {
		try {
			$property = Property::findOrFail( $propertyID );
			$detached = $property->features()->detach( $featureID );
			if ( true == $detached ) {
				$features = $this->getFeatures( $propertyID );

				return $this->respond(
					[
						'message' => 'Features removed successfully',
						'data'    => $this->featureTransformer->transformCollection( $features->all() )
					]
				);
			} else {
				return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( 'Feature not linked to Property' );
			}
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * @param $propertyID
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|mixed|static[]
	 * @throws ModelNotFoundException
	 */
	public function getFeatures( $propertyID ) {
		$features = $propertyID ? Property::findOrFail( $propertyID )->features : Feature::all();

		return $features;
	}

}
