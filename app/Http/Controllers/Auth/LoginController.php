<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Constants\Components\UserTypes;
use App\Http\Controllers\BaseController;
use Laravel\Socialite\Facades\Socialite;
use App\Constants\Components\SocialLoginProviders;
use App\Components\Services\IAuthenticationService;
use App\Components\Validators\IAuthenticationValidator;
use App\Components\Services\ISocialAuthenticationService;
use App\Components\Validators\ISocialAuthenticationValidator;

class LoginController extends BaseController
{
    private $_authenticationService;
    private $_authenticationValidator;
    private $_socialAuthenticationValidator; 
    private $_socialAuthenticationService;

    public function __construct(
        IAuthenticationService $authenticationService,
        IAuthenticationValidator $authenticationValidator,
        ISocialAuthenticationValidator $socialAuthenticationValidator,
        ISocialAuthenticationService $socialAuthenticationService
    )
    {
        $this->_authenticationService = $authenticationService;
        $this->_authenticationValidator = $authenticationValidator;
        $this->_socialAuthenticationValidator = $socialAuthenticationValidator;
        $this->_socialAuthenticationService = $socialAuthenticationService;
    }

    public function login(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        $validator = $this->_authenticationValidator->validateLogin([
            'username' => $username,
            'password' => $password,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            
            $token = $this->_authenticationService->authenticate($username, $password, UserTypes::PUBLIC_USER);
    
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($token);
    }

    public function refreshToken()
    {
        try {
            $token =  $this->_authenticationService->refresh_authentication();
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
        return $this->setJsonDataResponse($token);
    }

    public function logout()
    {
        $this->_authenticationService->logout();
        return $this->setJsonMessageResponse('Successfully logged out');
    }

    public function socialLogin(Request $request, $provider) 
    {
        $code = $request->code;

        $validator = $this->_socialAuthenticationValidator->validateCodeAndProvider([
            'code' => $code,
            'provider' => $provider 
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $token = $this->_socialAuthenticationService->authenticateProvider($provider, $code);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($token);
    }

    public function redirectToProvider($provider)
    {
      
        $validator = $this->_socialAuthenticationValidator->validateRedirectProvider([
            'provider' => $provider 
        ]);
      
        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        return Socialite::driver($provider)->redirect();

    }
}
