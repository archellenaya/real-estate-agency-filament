<?php

/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 03/07/2015
 * Time: 10:22
 */

namespace App\Niu\Transformers;


/**
 * Class PropertyTypeTransformer
 * @package app\Niu\Transformers
 */
class PropertyTypeTransformer extends Transformer
{

	/**
	 * @param $propertyType
	 *
	 * @return array
	 */
	public function transform($propertyType)
	{
		return [
			'id'                => $propertyType['id'],
			'code' 				=> $propertyType['code'],
			'description'       => $propertyType['description'],
			'sort'              => $propertyType['sort_sequence'],
			'property_type_group' => $propertyType['property_type_groupId'],
		];
	}
}
