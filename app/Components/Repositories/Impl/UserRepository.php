<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IUserRepository;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserRepository implements IUserRepository
{
    public function createUser($username, $email, $password = null, $user_type_id)
    {
        //check if user was deleted before
        $existingDeletedUser = User::where('email', $email)->withTrashed()->first();
        if (empty($existingDeletedUser) === false) {
            if ($existingDeletedUser->trashed()) {
                $existingDeletedUser->restore();
                $existingDeletedUser->update(['active' => 0, 'email_verified_at' => null]);
                return $existingDeletedUser;
            }
        }

        $data = [
            'username'      => $username,
            'email'         => $email,
            'user_type_id'  => $user_type_id,
        ];


        if (!empty($password)) {
            $data['password'] = bcrypt($password);
        }

        return  User::create($data);
    }

    public function deactivateUser($user_id): bool
    {
        $user = User::find($user_id);
        $user->update(['active' => 0]);
        return $user->delete() ? true : false;
        // return User::where('id', $user_id)->update(['active' => 0]);
    }

    public function activateUser($user_id)
    {
        return User::where('id', $user_id)->update(['active' => 1]);
    }

    public function setUserPassword($user_id, $password)
    {
        return User::where('id', $user_id)->update(['password' => $password]);
    }

    public function updateLastLogin($user_id)
    {
        return User::where('id', $user_id)->update(['last_login' => Carbon::now()]);
    }

    public function getUser($user_id)
    {
        return User::find($user_id);
    }

    public function getUserByUsername($username)
    {
        return User::where('username', $username)->first();
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function getActiveUsers($limit)
    {
        $users = User::where('active', 1);
        if (!empty($limit)) {
            $users->limit($limit);
        }
        return $users->get();
    }

    public function getUsersByType($type, $per_page = 10)
    {
        return User::leftJoin('user_types as ut', 'ut.id', 'users.user_type_id')
            ->where('ut.type', $type)
            ->select([
                'users.id',
                'users.username',
                'users.last_login',
                'users.email_verified_at',
                'users.active',
                'ut.type as user_type'
            ])
            ->paginate($per_page);
    }

    public function getUsersDatatableByType($type, $keyword = null, $orderBy, $order = 'asc', $per_page = 10)
    {
        $users = User::leftJoin('user_types as ut', 'ut.id', 'users.user_type_id')
            ->where('ut.type', $type)
            ->where(function ($query) use ($keyword) {
                $query->orWhere('users.id', $keyword);
                $query->orWhere("username", 'LIKE', "%$keyword%");
            });

        if (!empty($orderBy)) {
            $users = $users->orderBy($orderBy, $order);
        }

        $users = $users->select([
            'users.id',
            'users.username',
            'users.last_login',
            'users.email_verified_at',
            'users.active',
            'ut.type as user_type'
        ])->paginate($per_page);

        return $users;
    }

    public function deleteUser($user_id)
    {
        return User::where('id', $user_id)->delete();
    }

    public function getUsersByProvider($provider_id, $provider)
    {
        return User::where('provider_id', $provider_id)->where('provider', $provider)->first();
    }

    public function createNewUserFromSocialAccount($user_type_id, $email, $provider, $provider_id)
    {
        //check if user was deleted before
        $existingDeletedUser = User::where('email', $email)->withTrashed()->first();
        if (empty($existingDeletedUser) === false) {
            if ($existingDeletedUser->trashed()) {
                $existingDeletedUser->restore();
                return $existingDeletedUser;
            }
        }

        $data = [
            'email' => $email,
            'user_type_id' => $user_type_id,
            'provider' => $provider,
            'provider_id' => $provider_id,
            'email_verified_at' => Carbon::now(),
            'active' => 1
        ];

        return User::create($data);
    }

    public function addFavorite($user, $property)
    {
        $user->property_saved()->syncWithoutDetaching($property);
    }

    public function getUserWishlists($user)
    {
        return $user->property_saved()->get();
    }

    public function updateUser($user_id, $incoming_data)
    {
        return User::where('id', $user_id)->update($incoming_data);
    }

    public function detachedProperty($user)
    {
        return $user->property_saved()->detach();
    }

    public function updateWishListPropertyAlert(User $user, Property $property, bool $alertsOn)
    {
        return $user
            ->property_saved()
            ->updateExistingPivot(
                $property->id,
                ['alerts_on' => $alertsOn === true ? '1' : '0'],
                true
            );
    }
}
