<?php

namespace App\Http\Controllers;

/**
 * API6
 */

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Components\Services\IAccountService;
use App\Components\Services\IInterestService;
use App\Components\Services\IBuyerTypeService;
use App\Components\Validators\IAccountValidator;

class AccountController extends BaseController
{
    private $_accountService;
    private $_accountValidator;
    private $_buyerTypeService;
    private $_interestService;

    public function __construct(
        IAccountService $accountService,
        IAccountValidator $accountValidator,
        IBuyerTypeService $buyerTypeService,
        IInterestService $interestService
    ) {
        $this->_accountService = $accountService;
        $this->_accountValidator = $accountValidator;
        $this->_buyerTypeService = $buyerTypeService;
        $this->_interestService = $interestService;
    }

    public function createAccountFirstLog()
    {
        $data = [
            "buyer_types" => $this->_buyerTypeService->getBuyerTypeList(),
            "interests" => $this->_interestService->getInterestList(),
        ];

        return $this->setJsonDataResponse($data);
    }

    public function updateAccountFirstLog(Request $request)
    {
        $buyer_type_id = $request->buyer_type_id;
        $interest_id = $request->interest_id;
        $currency = $request->currency;
        $send_updates = $request->send_updates;

        $data = [
            'buyer_type_id'    => $buyer_type_id,
            'interest_id'      => $interest_id,
            'currency'         => $currency,
            'send_updates'     => $send_updates,
        ];

        $validator = $this->_accountValidator->validateFirstUpdateAccount($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_accountService->firstAccountUpdate($buyer_type_id, $interest_id, $currency, $send_updates);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse("Successfully updated");
    }


    public function getAccount()
    {
        try {
            $data = $this->_accountService->getProfileAccount();
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($data);
    }

    public function updateAccount(Request $request)
    {
        $prefix                 = $request->prefix;
        $first_name             = $request->name;
        $last_name              = $request->surname;
        $region                 = $request->region;
        $country                = $request->country;
        $prefix_contact_number  = $request->prefix_contact_number;
        $contact_number         = $request->contact_number;
        $buyer_type_id          = $request->buyer_type_id;
        $interest_id            = $request->interest_id;
        $currency               = $request->currency;
        $profile_image          = $request->file('profile_image');

        $data = [
            'prefix'                => $prefix,
            'first_name'            => $first_name,
            'last_name'             => $last_name,
            'region'                => $region,
            'country'               => $country,
            'prefix_contact_number' => $prefix_contact_number,
            'contact_number'        => $contact_number,
            'buyer_type_id'         => $buyer_type_id,
            'interest_id'           => $interest_id,
            'currency'              => $currency,
            'profile_image'         => $profile_image
        ];

        $validation = $this->_accountValidator->validateUpdateAccount($data);

        if ($validation->fails()) {
            return $this->setValidationErrorJsonResponse($validation->errors());
        }

        try {
            $image_filename = $this->handleProfileImageUpload($request);

            $data = $this->_accountService->updateAccount($prefix, $first_name, $last_name, $region, $country, $prefix_contact_number, $contact_number, $buyer_type_id, $interest_id, $currency, $image_filename);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse("Successfully updated");
    }

    public function deactivateAccount(Request $request)
    {
        $password = $request->password;

        $data = [
            'password' => $password,
        ];

        $validation = $this->_accountValidator->validateDeactivationAccount($data);

        if ($validation->fails()) {
            return $this->setValidationErrorJsonResponse($validation->errors());
        }

        try {
            $this->_accountService->deactivateAccount($password);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse("Successfully deactivated account");
    }

    public function changeAccountEmail(Request $request)
    {
        $password = $request->password;
        $email = $request->email;

        $data = [
            'password' => $password,
            'email' => $email
        ];

        $validation = $this->_accountValidator->validateAccountEmailChange($data);

        if ($validation->fails()) {
            return $this->setValidationErrorJsonResponse($validation->errors());
        }

        try {
            $this->_accountService->changeAccountEmail($password, $email);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function setNotification(Request $request)
    {
        $notify_property_price_change = $request->notify_property_price_change ?? null;
        $notify_property_sold = $request->notify_property_sold ?? null;

        $data = [
            'notify_property_price_change' => $notify_property_price_change,
            'notify_property_sold'         => $notify_property_sold
        ];

        $validation = $this->_accountValidator->validateNotificationChange($data);

        if ($validation->fails()) {
            return $this->setValidationErrorJsonResponse($validation->errors());
        }

        try {
            $this->_accountService->updateNotification($notify_property_price_change, $notify_property_sold);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function getNotification()
    {

        try {
            $settings = $this->_accountService->getNotificationSettings();
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
        return $this->setJsonDataResponse($settings);
    }
    private function handleProfileImageUpload(Request $request)
    {
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            $profile_image = $request->file('profile_image');
            $user = Auth::user();
            $image_filename = Str::slug(tenant('id') . " " . $user->id . " " . $user->profile->first_name)   . '.' . $profile_image->getClientOriginalExtension();
            Storage::disk('public')->put("users/" . $image_filename, file_get_contents($profile_image));
            return $image_filename;
        }

        return null;
    }
}
