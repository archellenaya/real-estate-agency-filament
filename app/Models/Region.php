<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_id',
        'description',
        'sequence_no',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function localities()
    {
        return $this->hasMany('App\Models\Locality', 'region_id', 'id');
    }
    public function zones()
    {
        return $this->hasMany('App\Models\Zone', 'region', 'id');
    }
}
