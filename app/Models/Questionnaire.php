<?php
/**
 * Created by PhpStorm.
 * User: omar
 * Date: 13/10/2015
 * Time: 09:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Questionnaire
 *
 * @property int $ID
 * @property string|null $buyer_ref
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $post_code
 * @property string|null $country
 * @property string|null $tel_1
 * @property string|null $tel_2
 * @property string|null $fax
 * @property string|null $email
 * @property string $consultants
 * @property string $inspection_date
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereBuyerRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereConsultants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereInspectionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire wherepost_code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereTel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereTel2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Questionnaire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Questionnaire extends Model {

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'buyer_ref',
        'title',
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'post_code',
        'country',
        'tel_1',
        'tel_2',
        'fax',
        'email',
        'consultants',
        'inspection_date'
    ];

}