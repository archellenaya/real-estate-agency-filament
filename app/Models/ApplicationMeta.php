<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMeta extends Model
{
    protected $fillable = [
        'application_id',
        'field_id',
        'meta_value',
        'user_id',
        'meta_group_id',
        'filename',
        'content_type'
    ];

    public function field() {
        return $this->belongsTo('App\Models\Field');
    }

    public function application() {
        return $this->belongsTo('App\Models\Application');
    }    
}
