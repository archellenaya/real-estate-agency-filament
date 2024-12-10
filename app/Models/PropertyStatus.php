<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PropertyStatus
 *
 * @property integer $id
 * @property string $description
 * @property integer $sort_sequence
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\property_status whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\property_status whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\property_status wheresort_sequence($value)
 * @method static \Illuminate\Database\Query\Builder|\App\property_status whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\property_status whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PropertyStatus extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'property_status';

	protected $fillable = [
		'description',
		'sort_sequence'
	];

	protected $hidden = [
        'created_at',
        'updated_at',
    ];

	public function properties() {
		return $this->hasMany( 'App\Models\Property', 'property_status_id_field', 'id' );
	}
}
