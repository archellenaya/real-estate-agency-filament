<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAttachment extends Model
{
    //
	protected $fillable = [
        'application_id',
        'attachment_name',
        'attachment_file',
        'attachment_type',
        'attachment_mime'
    ];

    public function application() {
        return $this->belongsTo('App\Models\Application');
    }  
}
