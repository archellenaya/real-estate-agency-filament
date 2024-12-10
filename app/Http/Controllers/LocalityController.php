<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Locality;
use App\Niu\Transformers\LocalityTransformer;
use Illuminate\Http\Request;
/**
 * Class LocalityController
 *
 * @package App\Http\Controllers
 */
class LocalityController extends ApiController {
/**
 * API5
 */
	/**
	 * @var LocalityTransformer
	 */
	protected $localityTransformer;

	protected $_default_options;
	/**
	 * @var array
	 */
	protected $apiMethods = [
		'index'   => [
			'keyAuthentication' => FALSE
		],
		'maltese' => [
			'keyAuthentication' => FALSE
		],
	];

	/**
	 * LocalityController constructor.
	 *
	 * @param LocalityTransformer $localityTransformer
	 */
	public function __construct( LocalityTransformer $localityTransformer ) {
		// parent::__construct();
		$this->localityTransformer = $localityTransformer;
		$defaults = explode(',', config('app.default_options'));
        $this->_default_options = array_map(function($item) {
            return strtolower(trim($item));
        }, $defaults);
	}

	public function index(Request $request) 
	{
		$id 		= $request->get( 'id' ) ?? null;
		$region 	= $request->get( 'region' ) ?? null;
		$regions 	= $request->get( 'regions' ) ?? null;
		$post_code 	= $request->get( 'post_code' ) ?? null;  

		$localities = Locality::whereNotIn('locality_name',$this->_default_options)->where("status", 1);
	
		if(isset($id)) {
			return $localities->where("id", $id)->first();
		}

		if(isset($region)) {
			$localities->where("region", $region);
		}

		if(isset($regions)) {
			$localities->whereIn("region", $regions);
		}

		if(isset($post_code)) {
			$localities->where("post_code", $post_code);
		}

		$localities->orderBy("locality_name", 'ASC');

		return  $localities->get();
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index2( $filter = NULL ) {
		if ( $filter == 'maltese' ) {
			return $this->maltese();
		} else {
			return $this->all_localities();
		}
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	private function all_localities() {
		$localities = Locality::all();

		return $this->respond(
			[
				'data' => $this->localityTransformer->transformCollection( $localities->all() )
			]
		);
	}


	/**
	 * @return mixed
	 */
	private function maltese() {
		$localities = Locality::where( 'zoneId', '<=', 100 );

		return $this->respond(
			[
				'data' => $this->localityTransformer->transformCollection( $localities->get()->toArray() )
			]
		);
	}

}
