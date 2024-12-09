<?php

namespace App\Niu\Transformers;

/**
 * Created by PhpStorm.
 * User: niumark
 * Date: 24/05/2015
 * Time: 16:09
 */



use App\Consultant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;
use App\Niu\Transformers\Transformer;
use App\Components\Repositories\Impl\LocalityRepository;


class PropertySearchTransformer extends Transformer
{

	private $featureTransformer;
	private $fileTransformer;
	private $consultantTransformer;

	/**
	 * $featureTransformer constructor.
	 *
	 * @param FeatureTransformer       $featureTransformer
	 * @param FileTransformer          $fileTransformer
	 * @param ConsultantTransformer    $consultantTransformer
	 * @param PropertyBlockTransformer $propertyBlockTransformer
	 */
	public function __construct(
		FeatureTransformer $featureTransformer,
		FileTransformer $fileTransformer,
		ConsultantTransformer $consultantTransformer
	) {
		$this->featureTransformer    = $featureTransformer;
		$this->fileTransformer       = $fileTransformer;
		$this->consultantTransformer = $consultantTransformer;
	}

	public function transform($property)
	{
		$locality = new LocalityRepository;
		$garage = DB::table('features')->select('id')->where('feature_value', 'Car Space')->first();
		if (isset($garage)) {
			$garage_value = DB::table('feature_property')->select('feature_value')->where('feature_id', $garage->id)->where('property_id', $property['id'])->first();
		}

		$image = [];

		if ($property->files && $file = $property->files->first()) {
			$image[] = [
				'file_id'            => $file['id'] ?? null,
				'property_id'        => $file['property_id'] ?? null,
				'file_name'          => $file['file_name_field'] ?? 'default.webp',
				'file_type'          => $file['file_type_field'] ?? 'MainImage',
				'sequence_no'        => $file['sequence_no_field'] ?? null,
				'mime'               => $file['mime'] ?? 'image/webp',
				'original_file_name' => $file['original_file_name'] ?? 'default.webp',
				'seo_url'            => $file['seo_url_field'] ?? null,
				'orig_path'          => $file['orig_image_src'] ?? config('url.property_thumbnail'),
				'url_field'          => $file['url_field'] ?? $file['orig_image_src'] ?? config('url.property_thumbnail'),
				'image_status_field' => $file['image_status_field'] ?? null
			];
		} else {
			$image[] = Utilities::getDefaultPropertyFileAttributes();
		}

		return [
			'slug'		 		 	 => $property['slug'],
			'ref'                    => $property['property_ref_field'],
			'webRef'                 => $property['id'],
			'marketStatus'           => $property['market_status_field'],
			'marketType'             => $property['market_type_field'],
			'price'                  => $property['price_field'],
			'priceOnRequest'         => $property['por_field'],
			'soleAgents'             => (bool) $property['sole_agents_field'],
			'bedrooms'               => $property['bedrooms_field'],
			'bathrooms'              => $property['bathrooms_field'],
			'area'                   => $property['area_field'],
			'region_details'         => $locality->getRegion($property['region_field']),
			'locality_details'       => DB::table('locality')->where('id', $property['locality_id_field'])->first(),
			'propertyType_details'   => DB::table('propertytype')->where('id', $property['property_type_id_field'])->first(),
			'garage'				 => $garage_value->feature_value ?? null,
			'consultant'             => Consultant::find($property['consultant_id']),
			'property_status_details' => DB::table('property_status')->where('id', $property['property_status_id_field'])->first(),
			'files'                  => $image,
		];
	}
}
