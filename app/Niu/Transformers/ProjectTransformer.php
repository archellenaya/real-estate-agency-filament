<?php
namespace App\Niu\Transformers;


class ProjectTransformer extends Transformer{
	/**
	 * @param $propertyType
	 *
	 * @return array
	 */
	public function transform( $project ) {
		return [
			'id'                => $project['id'],
			'old_id'        	=> $project['old_id'],
			'name'           	=> $project['name'],
			'summary'       	=> $project['summary'],
			'description'       => $project['description'],
			'filename'       	=> $project['filename'],
			'original_photo_url'    => $project['original_photo_url'],
			'status'       		=> $project['status'],
			'created_at'    	=> $project['created_at']->format('Y-m-d H:i'),
		];		
	}
}