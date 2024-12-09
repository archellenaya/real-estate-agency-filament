<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainPropertyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
		'description',
		'sequence_no', 
		'old_id',
        'is_commercial'
    ];
}
