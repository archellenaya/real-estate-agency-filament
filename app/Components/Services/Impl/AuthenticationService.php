<?php

namespace App\Components\Services\Impl;

use Carbon\Carbon;
use App\Constants\Http\StatusCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Components\Services\IUserService;
use App\Components\Passive\TokenGenerator;
use App\Components\Services\IProfileService;
use App\Components\Services\IUserTypeService;
use App\Constants\Components\UniqueLinkTypes;
use App\Components\Services\IUniqueLinkService;
use App\Components\Services\IAuthenticationService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Services\IEmailNotificationService;
use App\Constants\Components\NotificationTriggerValue;

class AuthenticationService implements IAuthenticationService
{
    private $_userService;
    private $_userTypeService;
    private $_uniqueLinkService;
    private $_emailNotificationService;
    private $_profileService;

    public function __construct(
        IUserService $userService,
        IUserTypeService $userTypeService,
        IUniqueLinkService $uniqueLinkService,
        IEmailNotificationService $emailNotificationService,
        IProfileService $profileService
    ) {
        $this->_userService = $userService;
        $this->_userTypeService = $userTypeService;
        $this->_uniqueLinkService = $uniqueLinkService;
        $this->_emailNotificationService = $emailNotificationService;
        $this->_profileService = $profileService;
    }

    public function authenticate($username, $password, $user_type)
    {
        $user_type_entity = $this->_userTypeService->getUserTypeByType($user_type);

        $user = $this->_userService->getUserByUsername($username);

        if (empty($user->email_verified_at) && !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_IS_NOT_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if (!empty($user->email_verified_at) && !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::ACCOUNT_DEACTIVATED,
                StatusCode::HTTP_UNAUTHORIZED
            );
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new ProcessException(
                ProcessExceptionMessage::INVALID_USER_CREDENTIALS,
                StatusCode::HTTP_UNAUTHORIZED
            );
        }

        $user->tokens()->delete();

        $this->_userService->updateLastLogin($user->id);

        $token = $user->createToken(
            'access-token',
            ['*'],
            now()->addMonth()
        )->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => now()->addMonth()
        ];
        // return TokenGenerator::respondWithToken($token);
    }

    public function refresh_authentication()
    {
        try {

            $token = auth()->refresh(true, true);
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::TOKEN_HAS_EXPIRED,
                StatusCode::HTTP_UNAUTHORIZED
            );
        }

        return TokenGenerator::respondWithToken($token);
    }

    public function register($firstname, $lastname, $username, $email, $password, $user_type)
    {
        // if ($password !== $password_confirmation) {
        //     throw new ProcessException(
        //         ProcessExceptionMessage::PASSWORD_MISMATCH,
        //         StatusCode::HTTP_BAD_REQUEST
        //     );
        // }

        DB::transaction(function () use (
            $firstname,
            $lastname,
            $username,
            $email,
            $password,
            $user_type
        ) {

            $user_type_entity = $this->_userTypeService->getUserTypeByType($user_type);

            $new_user = $this->_userService->createNewUser($user_type_entity->id, $username, $email, $password);

            $this->_profileService->addProfile($new_user->id, $firstname, $lastname);

            $unique_link = $this->_uniqueLinkService->createUniqueLink($new_user->id, UniqueLinkTypes::USER_REGISTRATION);

            //Send Email notification
            $recipients = [];
            $recipients[] = $email;

            $interpolation_properties = $this->getUserRegistrationInterpolationProperties($unique_link, $new_user);

            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_USER_REGISTRATION,
                $recipients
            );
        });
    }

    public function verifyAndActivateUser($code)
    {
        $unique_link = $this->_uniqueLinkService->getValidUniqueLinkByCode($code);

        $user = $unique_link->user;

        if (!empty($user->email_verified_at)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_ALREADY_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        DB::transaction(function () use (
            $user,
            $unique_link
        ) {
            $user->active = true;
            $user->email_verified_at = Carbon::now();
            $user->save();

            $this->_uniqueLinkService->processUniqueLink($unique_link->code);
        });
    }

    public function resendRegistrationEmailByEmail($email)
    {
        $user = $this->_userService->getUserByEmail($email);
    
        $this->resendRegistrationEmail($user);
    }

    public function resendRegistrationEmailByUserId($user_id)
    {
      
        $user = $this->_userService->getUser($user_id);
      
        $this->resendRegistrationEmail($user);
    }

    private function resendRegistrationEmail($user)
    {
        if (!empty($user->email_verified_at)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_ALREADY_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
      
        $uniqe_link = $this->_uniqueLinkService->getValidUniqueLinkForUser($user, UniqueLinkTypes::USER_REGISTRATION);

        DB::transaction(function () use (
            $uniqe_link,
            $user
        ) {
            if (!empty($uniqe_link)) {
                $this->_uniqueLinkService->invalidateUniqueLinkCode($uniqe_link);
            }

            $recipients = [];
            $recipients[] = $user->email;

            $new_unique_link = $this->_uniqueLinkService->createUniqueLink($user->id, UniqueLinkTypes::USER_REGISTRATION);

            if (empty($user->password)) {
                $interpolation_properties = $this->verifyRegistrationAndSetPasswordInterpolationProperties($new_unique_link, $user);
            } else {
                $interpolation_properties = $this->getUserRegistrationInterpolationProperties($new_unique_link, $user);
            }

            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_USER_REGISTRATION,
                $recipients
            );
        });
    }

    public function verifyAndSetPassword(
        $code,
        $password,
        $password_confirmation
    ) {
        $unique_link = $this->_uniqueLinkService->getValidUniqueLinkByCode($code);

        $user = $unique_link->user;

        if ($password !== $password_confirmation) {
            throw new ProcessException(
                ProcessExceptionMessage::PASSWORD_MISMATCH,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        DB::transaction(function () use (
            $user,
            $unique_link,
            $password
        ) {
            $user->password = bcrypt($password);
            $user->active = true;
            $user->email_verified_at = Carbon::now();
            $user->save();

            $this->_uniqueLinkService->processUniqueLink($unique_link->code);
        });
    }

    public function resetPasswordByUserId($user_id)
    {
        $user = $this->_userService->getUser($user_id);

        $this->sendResetPassword($user);
    }

    public function resetPasswordByEmail($email)
    {
        $user = $this->_userService->getUserByEmail($email);

        $this->sendResetPassword($user);
    }

    private function sendResetPassword($user, $query_string = '')
    {
        if (empty($user->email_verified_at) || !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_IS_NOT_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        $unique_link = $this->_uniqueLinkService->getValidUniqueLinkForUser($user, UniqueLinkTypes::RESET_PASSWORD);

        DB::transaction(function () use (
            $unique_link,
            $user,
            $query_string
        ) {
            if (!empty($unique_link)) {
                $this->_uniqueLinkService->invalidateUniqueLinkCode($unique_link);
            }

            $recipients = [];
            $recipients[] = $user->email;

            $new_unique_link = $this->_uniqueLinkService->createUniqueLink($user->id, UniqueLinkTypes::RESET_PASSWORD);

            $interpolation_properties = $this->getResetPasswordInterpolationProperties($new_unique_link, $user, $query_string);

            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_RESET_PASSWORD,
                $recipients
            );
        });
    }

    public function passwordReset(
        $code,
        $password,
        $password_confirmation
    ) {
        $unique_link = $this->_uniqueLinkService->getValidUniqueLinkByCode($code);

        $user = $unique_link->user;

        if (empty($user->email_verified_at) || !($user->active)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_IS_NOT_ACTIVE,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if ($password !== $password_confirmation) {
            throw new ProcessException(
                ProcessExceptionMessage::PASSWORD_MISMATCH,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        DB::transaction(function () use (
            $user,
            $unique_link,
            $password
        ) {
            $user->password = bcrypt($password);
            $user->password_last_update = Carbon::now();
            $user->save();

            $this->_uniqueLinkService->processUniqueLink($unique_link->code);
        });
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        // Auth::logout();
    }

    private function getResetPasswordInterpolationProperties(
        $uniqe_link,
        $user,
        $query_string = ''
    ) {
        $base_url = $this->getSendToUrlByUser($user);

        $url = "{$base_url}/?reset-pass-code={$uniqe_link->code}{$query_string}";

        return [
            'code' => $uniqe_link->code,
            'change_password_link' => $url,
            'email' => $user->email
        ];
    }

    private function getUserRegistrationInterpolationProperties(
        $uniqe_link,
        $user
    ) {
        $base_url = $this->getSendToUrlByUser($user);

        $url = "{$base_url}/verify/?code={$uniqe_link->code}";

        return [
            'user_activation_link' => $url,
            'email' => $user->email
        ];
    }

    private function verifyRegistrationAndSetPasswordInterpolationProperties(
        $uniqe_link,
        $user
    ) {
        $base_url = $this->getSendToUrlByUser($user);

        $url = "{$base_url}/verify/set/{$uniqe_link->code}";

        return [
            'user_activation_link' => $url,
            'email' => $user->email
        ];
    }

    private function getSendToUrlByUser($user)
    {
        $base_url = config('url.frontend_url');

        return $base_url;
    }
}
