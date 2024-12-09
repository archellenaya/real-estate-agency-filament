<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Consultant
 *
 * @package App
 * @property string $id
 * @property string $full_name_field
 * @property string $image_file_name_field
 * @property string $image_name_field
 * @property string $branch_id_field
 * @property string $description_field
 * @property string $designation_field
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property boolean $is_available
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant wherefull_name_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereimage_file_name_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereimage_name_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant wherebranch_id_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant wheredescription_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant wheredesignation_field($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Consultant whereis_available($value)
 * @property string $contact_number_field
 * @property string $email_field
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Consultant wherecontact_number_field($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Consultant whereemail_field($value)
 * @mixin \Eloquent
 */
class Consultant extends Model
{

	use HasFactory;
	/**
	 * @var array
	 */
	protected $fillable = [
		'id',
		'full_name_field',
		'image_file_name_field',
		'image_name_field',
		'branch_id_field',
		'description_field',
		'designation_field',
		'contact_number_field',
		'email_field',
		'is_available',
		'old_id',
		'office_phone_field',
		'orig_consultant_image_src',
		'external_id',
		'whatsapp_number_field',
		'agent_code',
		'data',
		'to_synch',
		'url_field',
		'image_status_field'
	];

	public $incrementing = false;

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function properties(): HasMany
	{
		return $this->hasMany(Property::class, 'consultant_id', 'id');
	}

	public function getID()
	{
		return $this->id;
	}
	public function branch(): BelongsTo
	{
		return $this->belongsTo(Branch::class, 'branch_id_field', 'id');
	}
	public function getBranch()
	{
		return Branch::find($this->branch_id_field);
	}
	public function getBranchName()
	{
		return Branch::where('id', $this->branch_id_field)->value('name');
	}

	public function getimage_file_name_field()
	{
		return $this->image_file_name_field;
	}
	public function scopeIsPublic($query)
	{
		return $query->where('is_available', 1);
	}
}
