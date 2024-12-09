<?php
namespace App\Niu\Transformers;


class PropertyAuditTransformer extends Transformer{
	/**
	 * @param $propertyType
	 *
	 * @return array
	 */
	public function transform( $propertyAudit ) {
		return [
			'id'                => $propertyAudit['id'],
			'event_type'        => $propertyAudit['event_type'],
			'changes'           => json_decode($propertyAudit['changes']),
			'property_id'       => $propertyAudit['property_id'],
			'created_at'        => $propertyAudit['created_at']->format('Y-m-d H:i'),
		];
	}
}