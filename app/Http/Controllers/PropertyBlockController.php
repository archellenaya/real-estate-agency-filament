<?php namespace App\Http\Controllers;
/**
 * API5
 */
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyBlockRequest;
use App\Http\Requests\UpdatePropertyBlockRequest;
use App\Models\PropertyBlock;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PropertyBlockController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index() {
		$propertyBlocks = PropertyBlock::all();

		return $this->respond( [
			'data' => $propertyBlocks
		] );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StorePropertyBlockRequest $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function store( StorePropertyBlockRequest $request ) {
		try {
			$propertyBlock = PropertyBlock::create( [
				'id'                => $request->input( 'PropertyBlockID' ),
				'development_id'    => $request->input( 'DevelopmentID' ),
				'short_description' => $request->input( 'ShortDescription' ),
				'title'             => $request->input( 'Title' ),
				'long_description'  => $request->input( 'LongDescription' ),
				'abstract'          => $request->input( 'Abstract' ),
				'latitude'          => $request->input( 'Latitude' ),
				'longitude'         => $request->input( 'Longitude' )
			] );

			return $this->respond( $propertyBlock );
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function show( $id ) {
		$propertyBlock = PropertyBlock::find( $id );

		if ( ! $propertyBlock ) {
			return $this->respondNotFound( 'Development does not exist' );
		}
		$propertyBlock->properties;

		return $this->respond( [
			'data' => $propertyBlock
		] );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int                        $id
	 * @param  UpdatePropertyBlockRequest $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function update( $id, UpdatePropertyBlockRequest $request ) {
		try {

			$propertyBlock = PropertyBlock::findOrFail( $id );
			$columns       = [
				'development_id'    => 'DevelopmentID',
				'short_description' => 'ShortDescription',
				'title'             => 'Title',
				'long_description'  => 'LongDescription',
				'abstract'          => 'Abstract',
				'latitude'          => 'Latitude',
				'longitude'         => 'Longitude'
			];
			$to_update     = [];
			foreach ( $columns as $column => $param ) {
				if ( $request->has( $param ) ) {
					$to_update[$column] = $request->input( $param );
				}
			}
			$propertyBlock->update( $to_update );
			$propertyBlock->updated_at = Carbon::now();
			$propertyBlock->save();

			return $this->respond( $propertyBlock );

		} catch ( Exception $e ) {

			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );

		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function destroy( $id ) {
		try {
			$propertyBlock = PropertyBlock::findOrFail( $id );
			$propertyBlock->delete();

			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $propertyBlock
				]
			);
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

}
