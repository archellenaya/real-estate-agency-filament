<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Property;


class File extends Model {

	/**
	 * @var array
	 */
	protected $fillable = [
		'property_id',
		'file_name_field',
		'file_type_field',
		'mime',
		'sequence_no_field',
		'original_file_name',
		'seo_url_field',
		'orig_image_src'
	];

	public function property(): BelongsTo
	{
		return $this->belongsTo(Property::class, 'property_id', 'id');
	}
}
