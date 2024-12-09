<?php

namespace App\Models\DTO;

class UserDTO
{
    public $id;
    public $username;
    public $email;
    public $active;
    public $email_verified_at;
    public $last_login;
    public $user_type;
    public $first_name;
    public $provider;

    public function __construct(
        $id, 
        $username, 
        $email,
        $active,
        $email_verified_at,
        $last_login,
        $user_type,
        $first_name, 
        $provider
    )
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->active = $active;
        $this->email_verified_at = $email_verified_at;
        $this->last_login = $last_login;
        $this->user_type = $user_type;
        $this->first_name = $first_name;
        $this->provider = $provider;
    }
}
