<?php

/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 02/06/2015
 * Time: 00:03
 */

namespace App\Niu\Transformers;

use App\Niu\Transformers\Transformer;
use App\Consultant;

class AgentCodeTransformer extends Transformer
{

	public function transform($consultant)
	{
		$consultant = Consultant::findOrFail($consultant['id']);
		// return $consultant->getBranch();
		return [
			'id'             	 	=> $consultant['id'] ?? null,
			'old_id'    	 	 	=> $consultant['old_id'] ?? null,
			'agentCode'    	 	 	=> $consultant['agent_code'] ?? null,
			'fullNameField'   	 	=> $consultant['full_name_field'] ?? null,
			'imageFilenameField' 	=> $consultant['image_file_name_field'] ?? 'default.webp',
			'imageNameField'     	=> $consultant['image_name_field'] ?? 'default.webp',
			'branch'      	  	 	=> $consultant->getBranch() ?? null,
			'designationField'   	=> $consultant['designation_field'] ?? null,
			'whatsappNumberField'	=> $consultant['whatsapp_number_field'] ?? null,
			'contactNumberField' 	=> $consultant['contact_number_field'] ?? null,
			'officePhoneField'   	=> $consultant['office_phone_field'] ?? null,
			'emailField'         	=> $consultant['email_field'] ?? null,
			'isAvailable'    	 	=> $consultant['is_available'] ?? null,
			'url_field'          	=> $consultant['url_field'] ??  $consultant['orig_consultant_image_src'] ?? config('url.consultant_thumbnail'),
			'image_status_field' 	=> $consultant['image_status_field'] ?? null,
			'sourcePhotoUrl'  	 	=> $consultant['orig_consultant_image_src'] ?? config('url.consultant_thumbnail'),
			'listingCounts' 	 	=> $consultant->properties()->count()
		];
	}
}
