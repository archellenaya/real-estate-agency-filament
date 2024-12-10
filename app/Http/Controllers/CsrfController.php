<?php
/**
 * API5
 */

namespace App\Http\Controllers;

 


/**
 * Class ApiController
 *
 * @package app\Http\Controllers
 */
class CsrfController extends ApiController {

	public function __construct()
    {
		$this->middleware(\App\Http\Middleware\VerifyCsrfToken::class);
		$this->middleware(\Illuminate\Session\Middleware\StartSession::class);
    }

	public function generateCSRFToken()
	{ 
		return $this->setJsonDataResponse(['csrf_token' => csrf_token()], 200);
	}
}