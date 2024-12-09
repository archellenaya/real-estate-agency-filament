<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IProfileRepository;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
class ProfileRepository implements IProfileRepository
{
    public function createProfile($user_id, $first_name, $last_name)
    {   
        $data = [
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ];

        $existingProfile = Profile::where('user_id', $user_id)->first();
        if($existingProfile){
            //this case is reached in case of user reactivation 
            $existingProfile->update($data);
            return $existingProfile;
        }

        return Profile::create($data);
    }

    public function updateProfile($user_id, $data)
    {
        return Profile::where('user_id', $user_id)->update($data);
    }
    
    public function getProfileByID($id)
    {
        return Profile::find($id);
    }

    public function createProfileFromSocialAccount($user_id, $first_name, $last_name, $contact_number = null) 
    {
        $data = [
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'contact_number' => $contact_number,
        ];

        $existingProfile = Profile::where('user_id', $user_id)->first();
        if($existingProfile){
            //this case is reached in case of user reactivation 
            $existingProfile->update($data);
            return $existingProfile;
        }

        return Profile::create($data);
    }

    public function getProfileByUserID($user_id) 
    {
        return Profile::where('user_id', $user_id)->first();
    }
}
