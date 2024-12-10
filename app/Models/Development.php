<?php

namespace App\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Development
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PropertyBlock[] $propertyBlocks
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Development whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Development whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Development whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Development whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Development extends Model
{

	public $autoincrement = false;
	public $incrementing = false;
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = [
		'id',
		'name'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function propertyBlocks()
	{
		return $this->hasMany('App\Models\PropertyBlock', 'development_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\hasManyThrough
	 */
	public function properties()
	{
		return $this->hasManyThrough(Property::class, 'App\Models\PropertyBlock', 'development_id', 'property_block_id_field');
	}
}
