<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{	

	protected $fillable = [
        'form_type_id',
        'status',
        'status_notes',
        'user_id',
    ];


    protected $dates = [
    	'approved_at'
    ];

    protected $with = array('user','metas','attachments','form_type');

    public function form_type() {
        return $this->belongsTo('App\Models\FormType');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }              

	public function metas()
	{
		return $this->hasMany('App\Models\ApplicationMeta');
	}

	public function attachments()
	{
		return $this->hasMany('App\Models\ApplicationAttachment');
	}

}
