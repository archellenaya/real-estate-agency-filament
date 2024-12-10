<?php
/**
 * API5
 */

namespace App\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Contracts\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ApiController
 *
 * @package app\Http\Controllers
 */
class ApiController extends BaseController {


	/**
	 * @var int
	 */
	protected $statusCode = Response::HTTP_OK;

	/**
	 * @return mixed
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @param mixed $statusCode
	 *
	 * @return $this
	 */
	public function setStatusCode( $statusCode ) {
		$this->statusCode = $statusCode;

		return $this;
	}

	/**
	 * @param       $data
	 * @param array $headers
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function respond( $data, $headers = [ ] ) {
		return \Response::json( $data, $this->getStatusCode(), $headers );
	}

	/**
	 * @param $properties
	 * @param $data
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function respondWithPagination( Paginator $properties, $data ) {
		$data = $this->getWithPagination( $properties, $data );

		return $this->respond( $data );
	}

	/**
	 * @param string $message
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function respondNotFound( $message = 'Not Found!' ) {
		return $this->setStatusCode( Response::HTTP_NOT_FOUND )->respondWithError( $message );
	}

	/**
	 * @param string $message
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function respondInternalError( $message = 'Internal Error!' ) {
		return $this->setStatusCode( Response::HTTP_INTERNAL_SERVER_ERROR )->respondWithError( $message );
	}

	/**
	 * @param string $message
	 *
	 * @return Response
	 */
	public function respondUnauthorised( $message = 'Unauthorised!' ) {
		return $this->setStatusCode( Response::HTTP_UNAUTHORIZED )->respondWithError( $message );
	}

	/**
	 * @param $message
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function respondWithError( $message ) {
		return $this->respond(
			[
				'error' => [
					'message'     => $message,
					'status_code' => $this->getStatusCode()
				]
			]
		);
	}

	/**
	 * @param Paginator $properties
	 * @param           $data
	 *
	 * @return array
	 */
	public function getWithPagination( Paginator $properties, $data ) {
		$data = array_merge(
			[
				'paginator' => [
					'total_count'  => $properties->total(),
					'total_pages'  => $properties->lastPage(),
					'current_page' => $properties->currentPage(),
					'limit'        => $properties->perPage(),
				]
			],
			$data
		);

		return $data;
	}
}