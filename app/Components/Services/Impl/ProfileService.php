<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IProfileService;
use App\Components\Repositories\IProfileRepository;
use Illuminate\Support\Facades\Auth;

class ProfileService implements IProfileService {

    private $_profileRepository;

    public function __construct(  IProfileRepository $profileRepository )
    {
        $this->_profileRepository = $profileRepository;
    }

    public function addProfile($user_id, $first_name, $last_name) {
        $this->_profileRepository->createProfile($user_id, $first_name, $last_name);
    }

    public function updateFirstTime($profile_data) {
        $user = Auth::user();
        return $this->_profileRepository->updateProfile($user->id, $profile_data);
    }

    public function addProfileFromSocialAccount($user_id, $first_name, $last_name, $contact_number = null) {
        $this->_profileRepository->createProfileFromSocialAccount($user_id, $first_name, $last_name);
    }

    public function getProfileByUserID($user_id) 
    {
        return $this->_profileRepository->getProfileByUserID($user_id);
    }

    public function updateProfileAccount($data) 
    {
        $user = Auth::user();
        return $this->_profileRepository->updateProfile($user->id, $data);
    }
}
