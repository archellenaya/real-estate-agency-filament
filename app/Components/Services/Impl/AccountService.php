<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IAccountService;
use App\Components\Services\IProfileService;
use App\Components\Services\IUserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AccountService implements IAccountService
{

    private $_profileService;
    private $_userService;

    public function __construct(IUserService $userService, IProfileService $profileService)
    {
        $this->_userService     = $userService;
        $this->_profileService  = $profileService;
    }

    public function firstAccountUpdate($buyer_type_id, $interest_id, $currency, $send_updates)
    {
        $subscription_data = [
            'send_updates' => $send_updates,
        ];

        $profile_data = [
            'buyer_type_id' => $buyer_type_id ?? null,
            'interest_id'   => empty($interest_id) === false &&  is_numeric($interest_id) === true ? $interest_id : null,
            'currency'      => $currency ?? null,
        ];

        DB::transaction(function () use ($subscription_data, $profile_data) {
            $this->_userService->updateSubscription($subscription_data);
            $this->_profileService->updateFirstTime($profile_data);
        });
    }

    public function getProfileAccount()
    {
        $user = $this->_userService->getAuthUser();
        $profile = $this->_profileService->getProfileByUserID($user->id);
        $data = [
            'prefix'                => $profile->prefix ?? null,
            'name'                  => $profile->first_name ?? null,
            'surname'               => $profile->last_name ?? null,
            'region'                => $profile->region ?? null,
            'country'               => $profile->country ?? null,
            'prefix_contact_number' => $profile->prefix_contact_number ?? null,
            'contact_number'        => $profile->contact_number ?? null,
            'buyer_type_id'         => $profile->buyer_type_id ?? null,
            'interest_id'           => $profile->interest_id ?? null,
            'currency'              => $profile->currency ?? null,
            'username'              => $user->username ?? null,
            'email'                 => $user->email ?? null,
            'profile_image_url'     => url('/' . tenant('id') . "/api/v1/image/user/$profile->profile_image_filename", [], true),
            'password_last_update'  => $user->password_last_update ?? null,
            'account_date_created'  => $user->created_at ?? null,
        ];

        return $data;
    }

    public function updateAccount(
        $prefix = null,
        $first_name,
        $last_name,
        $region        = null,
        $country        = null,
        $prefix_contact_number = null,
        $contact_number = null,
        $buyer_type_id  = null,
        $interest_id    = null,
        $currency       = null,
        $image_filename = null
    ) {
        $profileData = [
            'prefix'                    =>  $prefix,
            'first_name'                =>  $first_name,
            'last_name'                 =>  $last_name ?? '',
            'region'                    =>  $region,
            'country'                   =>  $country,
            'prefix_contact_number'     => $prefix_contact_number,
            'contact_number'            =>  $contact_number,
            'buyer_type_id'             =>  is_numeric($buyer_type_id) ? $buyer_type_id : 1, //no preference
            'interest_id'               =>  empty($interest_id) === false &&  is_numeric($interest_id) === true ? $interest_id : null,
            'currency'                  =>  $currency,
            'profile_image_filename'    => $image_filename
        ];

        try {
            $result = DB::transaction(function () use ($profileData) {
                $this->_profileService->updateProfileAccount($profileData);
            });
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_UPDATE_ACCOUNT,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $result;
    }

    public function deactivateAccount($password)
    {
        $user = $this->_userService->getAuthUser();

        if (empty($user->email_verified_at) || !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_IS_NOT_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if (!Hash::check($password, $user->password)) {
            throw new ProcessException(
                ProcessExceptionMessage::CURRENT_PASSWORD_INVALID,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        try {
            $result = DB::transaction(function () use ($user) {
                return $this->_userService->deactivateUser($user->id);
            });
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_UPDATE_ACCOUNT,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if ($result == 1) {
            Auth::logout();
        }
    }

    public function changeAccountEmail($password, $email)
    {
        $user = $this->_userService->getAuthUser();

        if (empty($user->email_verified_at) || !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_IS_NOT_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if (!Hash::check($password, $user->password)) {
            throw new ProcessException(
                ProcessExceptionMessage::CURRENT_PASSWORD_INVALID,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        try {
            $data = ['email' => $email];
            $result = DB::transaction(function () use ($user, $data) {
                return $this->_userService->updateUserAccount($data);
            });
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_UPDATE_ACCOUNT,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if ($result == 1) {
            Auth::user()->tokens()->delete();
        }
    }

    public function updateNotification($notify_property_price_change, $notify_property_sold)
    {
        try {
            $result = DB::transaction(function () use ($notify_property_price_change, $notify_property_sold) {
                $data = [
                    "notify_on_property_changes" => $notify_property_price_change,
                    "notify_on_property_sold" => $notify_property_sold
                ];
                return $this->_userService->updateUserAccount($data);
            });
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_UPDATE_ACCOUNT,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $result;
    }

    public function getNotificationSettings()
    {
        $settings = array();
        try {
            $user = $this->_userService->getAuthUser();
            $settings['notify_on_property_changes'] =  $user->notify_on_property_changes;
            $settings['notify_on_property_sold']    =  $user->notify_on_property_sold;
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_UPDATE_ACCOUNT,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $settings;
    }
}
