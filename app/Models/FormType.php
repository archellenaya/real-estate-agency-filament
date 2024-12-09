<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormType extends Model
{
 /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'key', 'slug', 'active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}