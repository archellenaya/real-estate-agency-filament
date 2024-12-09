<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Partner
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string|null $address
 * @property string $country
 * @property string|null $post_code
 * @property string $phone_1
 * @property string|null $phone_2
 * @property string|null $fax
 * @property string|null $summary
 * @property string $logo_file_name
 * @property string $partner_type
 * @property string $template
 * @property int $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $logo_link
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner wherelogo_file_name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereLogoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner wherepartner_type($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner wherephone_1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner wherephone_2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner wherepost_code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Partner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Partner extends Model {

	/**
	 * @var array
	 */
	protected $fillable = [
		'id',
		'name',
		'email',
		'address',
		'country',
		'post_code',
		'phone_1',
		'phone_2',
		'fax',
		'summary',
		'logo_file_name',
		'logo_link',
		'partner_type',
		'template',
		'active'
	];

}
