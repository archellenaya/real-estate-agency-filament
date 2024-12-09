<?php
/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 30/05/2015
 * Time: 15:34
 */

namespace App\Niu\Transformers;
use App\Niu\Transformers\Transformer;
class FeatureTransformer extends Transformer {

	public function transform( $feature ) {
		return [
			'id'          => $feature['id'] ?? null ,
			'sort_order'   => $feature['sort_order'] ?? null ,
			'feature'     => $feature['feature_value'] ?? null ,
			'is_available' => $feature['is_available']?? null  ,
			'value'       => $feature['pivot']['feature_value']?? null 
		];
	}
}