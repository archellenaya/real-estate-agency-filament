<?php namespace App\Http\Controllers;
/**
 * API5
 */
use App\Development;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDevelopmentRequest;
use App\Http\Requests\UpdateDevelopmentRequest;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\Request;

class DevelopmentController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function index() {
		$developments = Development::all();

		return $this->respond( [
			'data' => $developments
		] );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreDevelopmentRequest $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function store( StoreDevelopmentRequest $request ) {
		try {
			$development = Development::create( [
				'id'   => $request->input( 'DevelopmentID' ),
				'name' => $request->input( 'Name' )
			] );

			return $this->respond( $development );
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
		$development = Development::with(['propertyBlocks.properties'] )->find( $id );
		if ( ! $development ) {
			return $this->respondNotFound( 'Development does not exist' );
		}

		return $this->respond( [
			'data' => $development
		] );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int                      $id
	 * @param  UpdateDevelopmentRequest $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function update( $id, UpdateDevelopmentRequest $request ) {
		try {

			$development       = Development::findOrFail( $id );
			$development->update([
				'name' => $request->input( 'Name' )
			]);
			return $this->respond( $development );

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
			$development = Development::findOrFail( $id );
			$development->delete();

			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $development
				]
			);
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

}
