<?php

namespace App\Http\Controllers\Auth;

use App\Components\Services\IAuthenticationService;
use App\Components\Validators\IAuthenticationValidator;
use App\Constants\Components\UserTypes;
use App\Exceptions\ProcessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class RegisterController extends BaseController
{
    private $_authenticationService;
    private $_authenticationValidator;

    public function __construct(
        IAuthenticationService $authenticationService,
        IAuthenticationValidator $authenticationValidator
    ) {
        $this->_authenticationService = $authenticationService;
        $this->_authenticationValidator = $authenticationValidator;
    }

    public function register(Request $request)
    {
        $firstname = $request->firstname;
        $lastname  = $request->lastname;
        $username  = $request->username;
        $email     = $request->email;
        $password  = $request->password;
        // $password_confirmation = $request->password_confirmation;

        $validator = $this->_authenticationValidator->validateRegister([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'username'  => $username,
            'email'  => $email,
            'password'  => $password,
        ]);
        if ($validator->fails()) {

            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->register($firstname, $lastname, $username, $email, $password, UserTypes::PUBLIC_USER);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function resendVerificationEmail(Request $request)
    {
        $email = $request->email;

        $validator = $this->_authenticationValidator->validateEmail([
            'email' => $email,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->resendRegistrationEmailByEmail($email);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function verifyRegistration(Request $request)
    {
        $code = $request->code;

        $validator = $this->_authenticationValidator->validateCode([
            'code' => $code,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->verifyAndActivateUser($code);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function verifyRegistrationAndSetPassword(Request $request)
    {
        $code = $request->code;
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        $validator = $this->_authenticationValidator->validateCodeAndPassword([
            'code'                  => $code,
            'password'              => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->verifyAndSetPassword($code, $password, $password_confirmation);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }
}
