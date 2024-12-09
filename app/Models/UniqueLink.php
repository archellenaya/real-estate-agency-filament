<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniqueLink extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'date_expiry', 'date_processed', 'link_type_id', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function unique_link_type()
    {
        return $this->belongsTo('App\Models\UniqueLinkType', 'link_type_id', 'id');
    }
}