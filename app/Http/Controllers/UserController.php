<?php

namespace App\Http\Controllers;

use App\Components\Services\IUserService;
use App\Components\Validators\IUserValidator;
use App\Exceptions\ProcessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    private $_userService;
    private $_userValidator;
    
    public function __construct(
        IUserService $userService,
        IUserValidator $userValidator
    )
    {
        $this->_userService = $userService;
        $this->_userValidator = $userValidator;
    }

    public function getLoggedInUserDetails()
    {
        $user = auth()->user(); 
        try {
            $user = $this->_userService->getUserDTO($user->id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($user);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $current_password = $request->current_password;
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        $validator = $this->_userValidator->validateChangePassword([
            'current_password' => $current_password,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_userService->changePassword($user->id, $current_password, $password, $password_confirmation);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }
}
