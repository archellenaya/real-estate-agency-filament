<?php

namespace App\Components\Services\Impl;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Constants\Http\StatusCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Constants\Components\UserTypes;
use Laravel\Socialite\Facades\Socialite;
use App\Components\Services\IUserService;
use App\Components\Passive\TokenGenerator;
use App\Components\Services\IProfileService;
use App\Constants\Components\SocialLoginProviders;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Services\ISocialAuthenticationService;

class SocialAuthenticationService implements ISocialAuthenticationService
{
    private $_userService;
    private $_profileService;

    public function __construct(IUserService $userService, IProfileService $profileService)
    {
        $this->_userService = $userService;
        $this->_profileService = $profileService;
    }

    public function authenticateProvider($provider, $code)
    {
        switch ($provider) {
            case SocialLoginProviders::FACEBOOK:
                return $this->authenticateWithFacebookProvider($code);
            case SocialLoginProviders::GOOGLE:
                return $this->authenticateWithGoogleProvider($code);
            case SocialLoginProviders::LINKEDIN:
                return $this->authenticateWithLinkedInProvider();
            case SocialLoginProviders::APPLE:
                throw new ProcessException(
                    ProcessExceptionMessage::UNDER_DEVELOPMENT,
                    StatusCode::HTTP_BAD_REQUEST
                );
            default:
                throw new ProcessException(
                    ProcessExceptionMessage::PROVIDER_NOT_SUPPORTED,
                    StatusCode::HTTP_BAD_REQUEST
                );
        }
    }

    private function authenticateWithLinkedInProvider()
    {
        try {
            $user = Socialite::driver(SocialLoginProviders::LINKEDIN)->stateless()->user();

            $id = $user->getId();
            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

            return $this->createUserWithToken($email,  $firstName, $lastName, SocialLoginProviders::LINKEDIN, $id);
        } catch (\Exception $e) {
            Log::debug('Error: ' . $e->getMessage());
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_LOGIN . ' - ' . $e->getMessage(),
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }

    private function authenticateWithGoogleProvider($code)
    {
        try {
            $socialite = Socialite::driver(SocialLoginProviders::GOOGLE);
            $accessToken = $socialite->getAccessTokenResponse($code)['access_token'] ?? null;
            $user = $socialite->userFromToken($accessToken);
     
            $id = $user['sub'] ?? time();
            $email = $user['email'] ?? null;
            $firstName = $user['given_name'] ?? strtok($email, '@');
            $lastName = $user['family_name'] ?? '';

            return $this->createUserWithToken($email, $firstName, $lastName, SocialLoginProviders::GOOGLE, $id);
        } catch (\Exception $e) {
            Log::debug('Error: ' . $e->getMessage());
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_LOGIN . ' - ' . $e->getMessage(),
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }

    private function authenticateWithFacebookProvider($code)
    {
        try {
            $socialite = Socialite::driver(SocialLoginProviders::FACEBOOK);
            $accessToken = $socialite->getAccessTokenResponse($code);
            $user = $socialite->userFromToken($accessToken['access_token'] ?? null);

            $id = $user->getId();
            $email = $user->email;
            $name = $user->name;

            return $this->createUserWithToken($email, $name, '', SocialLoginProviders::FACEBOOK, $id);
        } catch (\Exception $e) {
            Log::debug('Error: ' . $e->getMessage());
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_LOGIN,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }

    private function createUserWithToken($email, $first_name, $last_name, $provider, $provider_id)
    {
        return DB::transaction(function () use ($email, $first_name, $last_name, $provider, $provider_id) {

            $email = empty($email) ? Str::random(8) . '@fakemail.com' : $email;
            $user = $this->_userService->getUserViaProvider($provider_id, $provider);

            if (empty($user)) {
                $user = $this->_userService->getUserByEmail($email);

                if (empty($user)) {
                    $user = $this->_userService->createNewUserFromSocialAccount(
                        UserTypes::PUBLIC_USER,
                        $email,
                        $provider,
                        $provider_id
                    );

                    $user->email_verified_at = Carbon::now();
                    $user->active = 1;
                    $this->_profileService->addProfileFromSocialAccount($user->id, $first_name, $last_name);
                }
            }

            if (empty($user->email)) {
                $user->email = $email;
            }

            $user->last_login = Carbon::now();
            $user->save();

            return TokenGenerator::createToken($user);
        });
    }

    public function generateUserName()
    {
        return substr(bin2hex(uniqid()), 0, 15);
    }
}
