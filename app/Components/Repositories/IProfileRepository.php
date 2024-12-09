<?php

namespace App\Components\Repositories;

interface IProfileRepository
{
    public function createProfile($user_id, $first_name, $last_name);

    public function getProfileByID($id);
    
    public function createProfileFromSocialAccount($user_id, $first_name, $last_name, $contact_number = null);

    public function getProfileByUserID($user_id);

    public function updateProfile($user_id, $data);
}
