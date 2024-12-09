<?php

namespace App\Components\Services;

interface IAuthenticationService
{

    public function authenticate($username, $password, $user_type);

    public function register($firstname, $lastname, $username, $email, $password, $user_type);

    public function verifyAndActivateUser($code);

    public function resendRegistrationEmailByEmail($username);

    public function resendRegistrationEmailByUserId($user_id);

    public function logout();

    public function verifyAndSetPassword(
        $code,
        $password,
        $password_confirmation
    );

    public function resetPasswordByUserId($user_id);

    public function resetPasswordByEmail($email);

    public function passwordReset(
        $code,
        $password,
        $password_confirmation
    );

    public function refresh_authentication();
}
