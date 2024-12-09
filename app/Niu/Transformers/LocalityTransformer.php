<?php
/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 03/07/2015
 * Time: 10:01
 */

namespace App\Niu\Transformers;


class LocalityTransformer extends Transformer {
	public function transform( $locality ) {
//		dd( $feature );

		return [
			'id'             => $locality['id'],
			'locality_name'   => $locality['locality_name'],
			'zone'           => $locality['zoneId'],
			'parentLocality' => $locality['parent_locality_id'],
		];
	}
}