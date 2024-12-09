<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
 /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_name', 
        'field_key', 
        'field_group', 
        'field_group_name',
        'sub_field_group',
        'sub_field_group_name',
        'main_field',
        'form_type_id',
        'active'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}