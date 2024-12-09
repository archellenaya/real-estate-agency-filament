<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 

class Locality extends Model {

	protected $table = 'locality';
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $fillable = [
		'locality_name',
		'zoneId',
		'parent_locality_id',
		'region',
		'post_code',
		'status',
		'old_id',
		'description',
		'sequence_no',
		'region_id', 
		'country_id'
	];

	public function properties()
    {
        return $this->hasMany('App\Models\Properties');
    }
	public function zone()
    {
        return $this->belongsTo('App\Models\Zone', 'zoneId', 'id');
    } 

	public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id', 'id');
    } 

	public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    } 
}