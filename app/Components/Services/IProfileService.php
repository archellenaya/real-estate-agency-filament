<?php

namespace App\Components\Services;

interface IProfileService
{
    public function addProfile($user_id, $first_name, $last_name);

    public function updateFirstTime($profile_data);

    public function addProfileFromSocialAccount($user_id, $first_name, $last_name, $contact_number = null);

    public function getProfileByUserID($user_id);

    public function updateProfileAccount($data);
}