<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branches';
        /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [  
        'id',
        'name', 
        'slug', 
        'email',
        'contact_number',
        'address',
        'coordinates',
        'display_order'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}