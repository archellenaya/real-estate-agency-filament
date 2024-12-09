<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_id',
        'description',
        'sort_sequence',
        'region',
        'country_id' 
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }

    public function region_model()
    {
        return $this->belongsTo('App\Models\Region', 'region', 'id');
    }

    public function localities()
	{
		return $this->hasMany('App\Models\Locality', 'zoneId', 'id');
	}
}