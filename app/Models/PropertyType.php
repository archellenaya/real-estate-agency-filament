<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'propertytype';

	protected $fillable = [
		'id',
		'code',
		'description',
		'sort_sequence',
		'property_type_groupId',
		'old_id',
		'meta_data'
	];

	protected $hidden = [
		'created_at',
		'updated_at',
	];

	protected $casts = [
		'meta_data' => 'array', // Cast JSON to array
	];

	public function properties()
	{
		return $this->hasMany('App\Models\Property');
	}

	public function property_type_group()
	{
		return $this->belongsTo('App\Models\PropertyTypeGroup', 'property_type_groupId', 'id');
	}

	public function propertySubTypes(): HasMany
	{
		return $this->hasMany(PropertySubType::class, 'propertytype_id', 'id');
	}
}
