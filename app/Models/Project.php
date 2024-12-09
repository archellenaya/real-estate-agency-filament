<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{

	protected $fillable = [
		'old_id',
		'name',
		'summary',
		'description',
		'filename',
		'original_photo_url',
		'status',
	];

	public function properties(): HasMany
	{
		return $this->hasMany(Property::class, 'project_id', 'id');
	}
}
