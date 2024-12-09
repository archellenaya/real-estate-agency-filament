<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'first_name', 
        'last_name', 
        'buyer_type_id', 
        'interest_id', 
        'currency', 
        'send_updates',
        'country',
        'contact_number',
        'prefix_contact_number',
        'prefix',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function buyer_type()
    {
        return $this->belongsTo('App\Models\BuyerType', 'buyer_type_id', 'id');
    }

    public function interest()
    {
        return $this->belongsTo('App\Models\Interest', 'interest_id', 'id');
    }
}