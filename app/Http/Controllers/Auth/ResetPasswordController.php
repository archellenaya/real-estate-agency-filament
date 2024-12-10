<?php

namespace App\Http\Controllers\Auth;

use App\Components\Services\IAuthenticationService;
use App\Components\Validators\IAuthenticationValidator;
use App\Exceptions\ProcessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ResetPasswordController extends BaseController
{
    private $_authenticationService;
    private $_authenticationValidator;

    public function __construct(
        IAuthenticationService $authenticationService,
        IAuthenticationValidator $authenticationValidator
    )
    {
        $this->_authenticationService = $authenticationService;
        $this->_authenticationValidator = $authenticationValidator;
    }

    public function sendPasswordReset(Request $request)
    {
        $email = $request->email;

        $validator = $this->_authenticationValidator->validateEmail([
            'email' => $email,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->resetPasswordByEmail($email);
        } catch (ProcessException $e){
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function passwordReset(Request $request)
    {
        $code = $request->code;
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        $validator = $this->_authenticationValidator->validateCodeAndPassword([
            'code' => $code,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        if ($validator->fails()) 
        {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_authenticationService->passwordReset($code, $password, $password_confirmation);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }
}