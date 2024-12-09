<?php

/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 02/06/2015
 * Time: 00:03
 */

namespace App\Niu\Transformers;

use Illuminate\Support\Facades\Log;

class FileTransformer extends Transformer
{

	public function transform($file)
	{
		return [
			'file_id'            => $file['id'],
			'property_id'        => $file['property_id'],
			'file_name'          => $file['file_name_field'],
			'file_type'          => $file['file_type_field'],
			'sequence_no'        => $file['sequence_no_field'],
			'mime'               => $file['mime'],
			'original_file_name' => $file['original_file_name'],
			'url_field'          => $file['url_field'] ?? $file['orig_image_src'],
			'image_status_field' => $file['image_status_field'],
			'seo_url'            => $file['seo_url_field'],
			'orig_path'          => $file['orig_image_src']
		];
	}
}
