<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_id',
        'description',
        'code',
        'sequence_no',
        'nationality'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function localities()
    {
        return $this->hasMany('App\Models\Locality', 'country_id', 'id');
    }
    public function zones()
    {
        return $this->hasMany('App\Models\Zone', 'country_id', 'id');
    }
}
