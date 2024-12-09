<?php 

namespace App\Components\Passive;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class TokenGenerator
{
    public static function generateApplicationPasscode()
    {
        return strtoupper(Str::random(6));
    }

    public static function generateUniqueLinkCode()
    {
        return substr(bin2hex(uniqid()), 0, 20);
    }

    public static function createToken($user)
    {
        $token = $user->createToken(
            'access-token', ['*'], now()->addMonth()
        )->plainTextToken;
        //Auth::factory()->getTTL() * 1
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => now()->addMonth()
        ];
    }

    public static function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => now()->addMonth()
        ];
    }
}