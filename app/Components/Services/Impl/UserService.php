<?php

namespace App\Components\Services\Impl;

use App\Components\Repositories\IUserRepository;
use App\Components\Services\IEmailNotificationService;
use App\Components\Services\IUniqueLinkService;
use App\Components\Services\IUserService;
use App\Components\Services\IUserTypeService;
use App\Components\Services\IProfileService;
use App\Components\Services\IPropertyService;
use App\Constants\Components\NotificationTriggerValue;
use App\Constants\Components\UniqueLinkTypes;
use App\Constants\Components\UserTypes;
use App\Constants\Http\StatusCode;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Exceptions\ProcessException;
use App\Models\DTO\UserDTO;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService implements IUserService
{
    private $_userRepository;
    private $_userTypeService;
    private $_uniqueLinkService;
    private $_emailNotificationService;
    private $_profileService;
    private $_propertyService;

    public function __construct(
        IUserRepository $userRepository,
        IUserTypeService $userTypeService,
        IUniqueLinkService $uniqueLinkService,
        IEmailNotificationService $emailNotificationService,
        IProfileService $profileService,
        IPropertyService $propertyService
    ) {
        $this->_userRepository = $userRepository;
        $this->_userTypeService = $userTypeService;
        $this->_uniqueLinkService = $uniqueLinkService;
        $this->_emailNotificationService = $emailNotificationService;
        $this->_profileService = $profileService;
        $this->_propertyService = $propertyService;
    }

    public function getUser($user_id)
    {
        $user = $this->_userRepository->getUser($user_id);

        if (empty($user)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $user;
    }

    public function getAuthUser()
    {
        $user_auth = Auth::user();
        $user = User::find($user_auth->id);
        if (empty($user)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $user;
    }

    public function getUserProfile($user_id)
    {
        $profile = $this->_profileService->getProfileByUserID($user_id);
        if (empty($profile)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_DOES_NOT_HAVE_PROFILE_INFO,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $profile;
    }

    public function getUserByUsername($username)
    {

        $user = $this->_userRepository->getUserByUsername($username);
        if (empty($user)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $user;
    }

    public function getUserByEmail($username)
    {
        return $this->_userRepository->getUserByEmail($username);
    }

    public function getUserDTO($user_id)
    {
        $user = $this->getUser($user_id);
        $profile = $this->getUserProfile($user_id);

        $userDTO = new UserDTO(
            $user->id,
            $user->username,
            $user->email,
            $user->active,
            $user->email_verified_at,
            $user->last_login,
            $user->user_type->type ?? '',
            $profile->first_name ?? '',
            $user->provider ?? null,
        );

        return $userDTO;
    }

    public function createNewUser($user_type_id, $username, $email, $password = null)
    {
        if ($this->doesUserExist($email)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_ALREADY_EXISTS,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
      
        $user_type = $this->_userTypeService->getUserTypeById($user_type_id);
        
        return $this->_userRepository->createUser($username, $email, $password, $user_type->id);
    }

    public function createAdminUser($firstname, $lastname, $username, $password = null)
    {
        DB::transaction(function () use ($username, $password, $firstname, $lastname) {
            $user_type = $this->_userTypeService->getUserTypeByType(UserTypes::BACKEND_USER);

            $new_user = $this->createNewUser($user_type->id, $firstname, $lastname, $username, $password);

            $unique_link = $this->_uniqueLinkService->createUniqueLink($new_user->id, UniqueLinkTypes::USER_REGISTRATION);

            //Send Email notification
            $recipients = [];
            $recipients[] = $username;

            $interpolation_properties = $this->verifyRegistrationAndSetPasswordInterpolationProperties($unique_link, $new_user);

            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_USER_REGISTRATION,
                $recipients
            );
        });
    }

    public function doesUserExist($email)
    {
        return !empty($this->_userRepository->getUserByEmail($email));
    }

    public function updateLastLogin($user_id)
    {
        $user = $this->getUser($user_id);
        $user->last_login = Carbon::now();
        $user->save();
    }

    public function deactivateUser($user_id)
    {
        $user = auth()->user();

        $userDeleted = $this->_userRepository->deactivateUser($user_id);
        if (!$userDeleted) {
            return false;
        }

        if (strpos($user->username, 'fake') !== false) {
            Log::warning('Deactivation email not sent to user with fake email, user: ' . json_encode($user));
            return true;
        }

        $this->_emailNotificationService->createEmailNotification(
            $user,
            NotificationTriggerValue::ON_USER_DEACTIVATION,
            [$user->username]
        );
    }

    public function activateUser($user_id)
    {
        $user = $this->getUser($user_id);
        $user->active = 1;
        $user->save();
    }

    public function setUserPassword($user_id, $password)
    {
        $user = $this->getUser($user_id);
        $user->password = $password;
        $user->save();
    }

    public function getActiveUsers($limit)
    {
        return $this->_userRepository->getActiveUsers($limit);
    }

    public function getUsersByType($type, $page_page = 10)
    {
        $user_type = $this->_userTypeService->getUserTypeByType($type);
        return $this->_userRepository->getUsersByType($user_type->type, $page_page);
    }

    public function getUsersDatatableByType($type, $keyword = null, $orderBy, $order = 'asc', $page_page = 10)
    {
        $user_type = $this->_userTypeService->getUserTypeByType($type);
        return $this->_userRepository->getUsersDatatableByType($user_type->type, $keyword, $orderBy, $order, $page_page);
    }

    public function changePassword($user_id, $current_password, $password, $password_confirmation)
    {
        $user = $this->getUser($user_id);

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

        if (!Hash::check($current_password, $user->password)) {
            throw new ProcessException(
                ProcessExceptionMessage::CURRENT_PASSWORD_INVALID,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        $user->password = bcrypt($password);
        $user->password_last_update = Carbon::now();

        $user->save();
    }

    public function deleteUser($user_id)
    {
        $user = $this->getUser($user_id);
        $user->delete();
    }

    public function isAdminUser($user_id)
    {
        $user = $this->getUser($user_id);

        return $user->user_type->type == UserTypes::BACKEND_USER;
    }

    private function verifyRegistrationAndSetPasswordInterpolationProperties(
        $uniqe_link,
        $user
    ) {
        $base_url = config('url.frontend_url');

        $url = "{$base_url}/verify/set/{$uniqe_link->code}";

        return [
            'user_activation_link' => $url,
            'username' => $user->username
        ];
    }

    public function createNewUserFromSocialAccount($user_type, $username, $provider, $provider_id = null)
    {
        if ($this->doesUserExist($username)) {
            throw new ProcessException(
                ProcessExceptionMessage::USER_ALREADY_EXISTS,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        $user_type_entity = $this->_userTypeService->getUserTypeByType($user_type);
        return $this->_userRepository->createNewUserFromSocialAccount($user_type_entity->id, $username, $provider, $provider_id);
    }

    public function getUserViaProvider($provider_id, $provider)
    {
        return $this->_userRepository->getUsersByProvider($provider_id, $provider);
    }

    public function addFavoriteProperty($user, $property)
    {
        $this->_userRepository->addFavorite($user, $property);
    }

    public function removeFavoriteProperty($user, $property)
    {
        $this->_propertyService->detachUserFromProperty($user, $property);
    }

    public function wishLists($user)
    {
        return $this->_userRepository->getUserWishlists($user);
    }

    public function updateWishListPropertyAlert(User $user, Property $property, bool $alertsOn)
    {
        $this->_userRepository->updateWishListPropertyAlert($user, $property, $alertsOn);
    }

    public function updateSubscription($subscription_data)
    {
        $user = $this->getAuthUser();
        return $this->_userRepository->updateUser($user->id, $subscription_data);
    }

    public function updateUserAccount($data)
    {
        $user = $this->getAuthUser();
        return $this->_userRepository->updateUser($user->id, $data);
    }

    public function clearWishLists()
    {
        $user = $this->getAuthUser();
        return $this->_userRepository->detachedProperty($user);
    }
}
