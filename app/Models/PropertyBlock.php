<?php

namespace App\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PropertyBlock
 *
 * @property int                                                           $id
 * @property int                                                           $development_id
 * @property string                                                        $short_description
 * @property string                                                        $title
 * @property string                                                        $long_description
 * @property string                                                        $abstract
 * @property string                                                        $latitude
 * @property string                                                        $longitude
 * @property \Carbon\Carbon                                                $created_at
 * @property \Carbon\Carbon                                                $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereAbstract( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereDevelopmentId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereLatLong( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereLongDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereShortDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereTitle( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereUpdatedAt( $value )
 * @mixin \Eloquent
 * @property-read \App\Development $development
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PropertyBlock whereLongitude($value)
 */
class PropertyBlock extends Model
{

	public $autoincrement = false;
	public $incrementing = false;
	public $timestamps = true;
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = [
		'id',
		'property_block_id',
		'development_id',
		'short_description',
		'title',
		'long_description',
		'abstract',
		'latitude',
		'longitude',
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function properties()
	{
		return $this->hasMany(Property::class, 'property_block_id_field', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function development()
	{
		return $this->BelongsTo('App\Development', 'development_id');
	}
}
