<?php

namespace App\Components\Repositories;

use App\Models\Property;
use App\Models\User;

interface IUserRepository
{
    public function createUser($username, $email, $password = null, $user_type_id);

    public function deactivateUser($user_id);

    public function activateUser($user_id);

    public function updateLastLogin($user_id);

    public function setUserPassword($user_id, $password);

    public function getUser($user_id);

    public function getUserByUsername($username);

    public function getUserByEmail($email);

    public function getActiveUsers($limit);

    public function getUsersByType($type, $per_page = 10);

    public function getUsersDatatableByType($type, $keyword = null, $orderBy, $order = 'asc', $per_page = 10);

    public function deleteUser($user_id);

    public function getUsersByProvider($provider_id, $provider);

    public function createNewUserFromSocialAccount($user_type_id, $email, $provider, $provider_id);

    public function addFavorite($user, $property);

    public function getUserWishlists($user);

    public function updateUser($user, $data);

    public function detachedProperty($user_id);

    public function updateWishListPropertyAlert(User $user, Property $property, bool $alertsOn);
}
