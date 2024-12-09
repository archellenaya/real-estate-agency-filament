<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use OwenIt\Auditing\Redactors\LeftRedactor;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable //implements  JWTSubject
{
    use  HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    // protected $attributeModifiers = [
    //     'password' => LeftRedactor::class,
    // ];

    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }

    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'last_login',
        'active',
        'user_type_id',
        'email_verified_at',
        'provider',
        'provider_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function user_type()
    {
        return $this->belongsTo('App\Models\UserType', 'user_type_id', 'id');
    }

    public function profile()
    {
        return $this->hasOne('App\Models\Profile', 'user_id', 'id');
    }

    public function property()
    {
        return $this->hasMany(Property::class);
    }

    public function property_alerts()
    {
        return $this->hasMany(PropertyAlert::class);
    }

    public function saved_searches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function property_saved()
    {
        return $this->belongsToMany(Property::class, 'user_property', 'user_id', 'property_id')->withPivot('alerts_on');
    }
}
