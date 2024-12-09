<?php

namespace App\Components\Services;

use App\Models\Property;
use App\Models\User;

interface IUserService
{
    public function getUser($user_id);

    public function getAuthUser();

    public function getUserByUsername($username);

    public function getUserByEmail($username);

    public function getUserDTO($user_id);

    public function createNewUser($user_type_id, $username,  $email, $password = null);

    public function createAdminUser($firstname, $lastname, $username, $password = null);

    public function updateLastLogin($user_id);

    public function deactivateUser($user_id);

    public function activateUser($user_id);

    public function setUserPassword($user_id, $password);

    public function getActiveUsers($limit);

    public function getUsersByType($type, $page_page = 10);

    public function getUsersDatatableByType($type, $keyword = null, $orderBy, $order = 'asc', $page_page = 10);

    public function deleteUser($user_id);

    public function doesUserExist($username);

    public function isAdminUser($user_id);

    public function changePassword($user_id, $current_password, $password, $password_confirmation);

    public function createNewUserFromSocialAccount($user_type_id, $email, $provider, $provider_id = null);

    public function getUserViaProvider($provider_id, $provider);

    public function addFavoriteProperty($user, $property);

    public function removeFavoriteProperty($user, $property);

    public function wishLists($user);

    public function updateSubscription($subscription_data);

    public function updateUserAccount($data);

    public function clearWishLists();

    public function updateWishListPropertyAlert(User $user, Property $property, bool $alertsOn);
}
