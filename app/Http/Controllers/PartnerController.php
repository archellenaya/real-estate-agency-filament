<?php namespace App\Http\Controllers;
/**
 * API5
 */
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Http\Requests\UpdateLogoRequest;
use App\Niu\Transformers\PartnerTransformer;
use App\Partner;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerController extends ApiController {

	/**
	 * @var $partnerTransformer PartnerTransformer
	 */
	protected $partnerTransformer;

	/**
	 * level of protection
	 *
	 * @var array
	 */
	protected $apiMethods = [
		'index'   => [
			'level' => 10
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
	 * PartnerController constructor.
	 *
	 * @param PartnerTransformer $partnerTransformer
	 */
	public function __construct( PartnerTransformer $partnerTransformer ) {
		// parent::__construct();
		$this->partnerTransformer = $partnerTransformer;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Response
	 */
	public function index() {
		$partners = Partner::all();

		return $this->respond(
			[
				'data' => $this->partnerTransformer->transformCollection( $partners->all() )
			]
		);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StorePartnerRequest $request
	 *
	 * @return \Response
	 */
	public function store( StorePartnerRequest $request ) {
		try {
			$logo = $request->file( 'logo' );

			// required to compute name or save image
			$timestamp = time();
			$extension = $logo->getClientOriginalExtension();
			$base_url  = $request->input( 'base_url' );

			$image_name = $base_url . '_' . $timestamp . '.' . $extension;

			// before creating entry in database so that if an error is thrown the entry will not be saved
			$logo->move( 'uploads/partners', $image_name );

			$file = Partner::create(
				[
					'id'           => $base_url,
					'name'         => $request->input( 'name' ),
					'email'        => $request->input( 'email' ),
					'address'      => $request->input( 'address' ),
					'country'      => $request->input( 'country' ),
					'post_code'     => $request->input( 'post_code' ),
					'phone_1'       => $request->input( 'phone_1' ),
					'phone_2'       => $request->input( 'phone_2' ),
					'fax'          => $request->input( 'fax' ),
					'summary'      => $request->input( 'summary' ),
					'partner_type' => $request->input( 'partner_type' ),
					'logo_file_name' => $image_name,
					'logo_link'    => $request->input( 'logo_link' ),
					'template'     => $request->input( 'template' ),
					'active'       => $request->input( 'active' ),
				]
			);

			return $this->respond( $file->toArray() );
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Response
	 */
	public function show( $id ) {
		/**
		 * @var $partner Partner
		 */
		$partner = Partner::findOrFail( $id );

		return $this->respond(
			[
				'data' => $this->partnerTransformer->transform( $partner )
			]
		);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int                $id
	 * @param UpdatePartnerRequest $request
	 *
	 * @return \Response
	 */
	public function update( $id, UpdatePartnerRequest $request ) {
		try {
			// required to compute name or save image
			$file = Partner::updateOrCreate(
				[
					'id' => $id
				],
				[
					'id'           => $request->input( 'base_url' ),
					'name'         => $request->input( 'name' ),
					'email'        => $request->input( 'email' ),
					'address'      => $request->input( 'address' ),
					'country'      => $request->input( 'country' ),
					'post_code'     => $request->input( 'post_code' ),
					'phone_1'       => $request->input( 'phone_1' ),
					'phone_2'       => $request->input( 'phone_2' ),
					'fax'          => $request->input( 'fax' ),
					'summary'      => $request->input( 'summary' ),
					'partner_type' => $request->input( 'partner_type' ),
					'logo_link'    => $request->input( 'logo_link' ),
					'template'     => $request->input( 'template' ),
					'active'       => $request->input( 'active' ),
				]
			);

			return $this->respond( $file->toArray() );
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

    /**
     * @param $id
     * @param UpdateLogoRequest $request
     * @return Response
     */
    public function UpdateLogo( $id, UpdateLogoRequest $request ){
        try {
            $logo = $request->file( 'logo' );

            // required to compute name or save image
            $timestamp = time();
            $extension = $logo->getClientOriginalExtension();
            $base_url  = $request->input( 'base_url' );

            $image_name = $base_url . '_' . $timestamp . '.' . $extension;

            // before creating entry in database so that if an error is thrown the entry will not be saved
            $logo->move( 'uploads/partners', $image_name );

            $file = Partner::updateOrCreate(
                [
                    'id' => $id
                ],
                [
                    'logo_file_name' => $image_name,
                ]
            );
            return $this->respond( $file->toArray() );
        } catch( Exception $e ){
            return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
        }
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Response
	 */
	public function destroy( $id ) {
		try {
			/**
			 * @var $partner Partner
			 */
			$partner = Partner::findOrFail( $id );
			$partner->delete();

			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $this->partnerTransformer->transform( $partner )
				]
			);
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

}
