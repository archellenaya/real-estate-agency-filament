<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAlert extends Model
{
    protected $fillable = [
        'name',
        'type_id',
        'property_type_id',
        'location_id',
        'min_price',
        'max_price',
        'user_id'
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Type', 'type_id', 'id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id','id');
    }
}