<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAudit extends Model {

	//
	protected $fillable = [
		'event_type',
		'changes',
		'property_id'
	];
}
