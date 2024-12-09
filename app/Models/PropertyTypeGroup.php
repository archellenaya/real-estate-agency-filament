<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

 
class PropertyTypeGroup extends Model 
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'property_type_group';

	protected $fillable = [
        'id',
		'description',
		'sequence_no', 
		'code',
		'commercial',
		'old_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

	public function property_types()
	{
		return $this->hasMany('App\Models\PropertyType',  'property_type_groupId', 'id');
	}
 
}