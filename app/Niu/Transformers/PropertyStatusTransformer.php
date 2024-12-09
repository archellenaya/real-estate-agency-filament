<?php
/**
 * Created by PhpStorm.
 * User: niumark
 * Date: 15/07/2015
 * Time: 18:03
 */

namespace App\Niu\Transformers;

use App\Niu\Transformers\Transformer;

class PropertyStatusTransformer extends Transformer{
	/**
	 * @param $propertyType
	 *
	 * @return array
	 */
	public function transform( $propertyType ) {
		return [
			'id'                => $propertyType['id'],
			'description'       => $propertyType['description'],
			'sort'              => $propertyType['sort_sequence'],
		];
	}
}