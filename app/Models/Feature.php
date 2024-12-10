<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Feature
 *
 * @property integer $id
 * @property integer $sort_order
 * @property string $feature_value
 * @property boolean $is_available
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature wheresort_order($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature wherefeature_value($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature whereis_available($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Feature extends Model {

	protected $fillable = [
		'sort_order',
		'feature_value',
		'is_available'
	];

	protected $hidden = [
        'created_at',
        'updated_at',
    ];

	public function properties()
    {
        return $this->belongsToMany('App\Models\Properties');
    }

}
