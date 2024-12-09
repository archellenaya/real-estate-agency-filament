<?php namespace App\Niu\Transformers;
/**
 * Created by PhpStorm.
 * User: niumark
 * Date: 14/08/15
 * Time: 18:02
 */



class PartnerTransformer extends Transformer {

	public function transform( $partner ) {
		return [
			'partner'      => $partner['id'],
			'name'         => $partner['name'],
			'email'        => $partner['email'],
			'address'      => $partner['address'],
			'country'      => $partner['country'],
			'post_code'    => $partner['post_code'],
			'phone_1'      => $partner['phone_1'],
			'phone_2'      => $partner['phone_2'],
			'fax'          => $partner['fax'],
			'summary'      => $partner['summary'],
			'logo'         => $partner['logo_file_name'],
			'logo_link'    => $partner['logo_link'],
			'partner_type' => $partner['partner_type'],
			'template'     => $partner['template'],
			'is_active'    => $partner['active'],
		];
	}
}