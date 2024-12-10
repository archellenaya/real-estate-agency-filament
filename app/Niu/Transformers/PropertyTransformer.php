<?php

namespace App\Niu\Transformers;

/**
 * Created by PhpStorm.
 * User: niumark
 * Date: 24/05/2015
 * Time: 16:09
 */



use App\Models\Consultant;
use App\Models\PropertyBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;
use App\Niu\Transformers\Transformer;
use App\Components\Repositories\Impl\LocalityRepository;


class PropertyTransformer extends Transformer
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
		FileTransformer $fileTransformer
	) {
		$this->featureTransformer    = $featureTransformer;
		$this->fileTransformer       = $fileTransformer;
	}

	public function transform($property)
	{
		$garage = $property->features->firstWhere('feature_value', 'Car Space');
		if ($garage) {
			$garage_value = $garage->pivot->feature_value;
		}

		$consultant_transformed = [];
		// if(isset($aid)) { //agent_code_id
		//     $consultant = Consultant::where('agent_code', $aid)->first(); 
		//     if(empty($consultant)) { 
		//         $consultant = Consultant::find( $aid ); 
		//     } 
		// } else {
		if (isset($property['consultant_id'])) {
			$consultant = $property->consultant;
		}
		// }

		if (!empty($consultant)) {

			$consultant_transformed = [
				'id'             => $consultant->id ?? null,
				'old_id'    	 => $consultant->old_id ?? null,
				'fullNameField'   => $consultant->full_name_field ?? null,
				'imageFilenameField' => $consultant->image_file_name_field ?? 'default.webp',
				'imageNameField'       => $consultant->image_name_field ?? 'default.webp',
				'branchId'      => $consultant->branch_id_field ?? null,
				'descriptionField'    => $consultant->description_field ?? null,
				'designationField'    => $consultant->designation_field ?? null,
				'whatsappNumberField'    => $consultant->whatsapp_number_field ?? null,
				'contactNumberField' => $consultant->contact_number_field ?? null,
				'officePhoneField'   => $consultant->office_phone_field ?? null,
				'emailField'          => $consultant->email_field ?? null,
				'isAvailable'    => $consultant->is_available ?? null,
				'sourcePhotoUrl'  => $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
				"image_status_field" => $consultant->image_status_field ?? null,
				"url_field" =>  $consultant->url_field ?? $consultant->orig_consultant_image_src ??  config('url.consultant_thumbnail'),
				'listingCounts' => $consultant->properties_count ?? null
			];
		}

		$files = $property->files->toArray();
		$files_transformed = [];

		if (!empty($files)) {
			foreach ($files as $file) {
				$files_transformed[] = [
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
		} else {
			$files_transformed[] = Utilities::getDefaultPropertyFileAttributes('MainImage');
			$files_transformed[] = Utilities::getDefaultPropertyFileAttributes('OtherImages');
		}


		return [
			'slug'		 		 	 => $property['slug'],
			'ref'                    => $property['property_ref_field'],
			'webRef'                 => $property['id'],
			'marketStatus'           => $property['market_status_field'],
			'expiry_date_time'       => $property['expiry_date_time'],
			'marketType'             => $property['market_type_field'],
			'commercial'             => $property['commercial_field'],
			'region'             	 => $property['region_field'],
			'region_details'         => $property->region,
			'locality'               => $property['locality_id_field'],
			'locality_details'       => $property->locality,
			'propertyType'           => $property['property_type_id_field'],
			'propertyType_details'   => $property->property_type,
			'property_status'         => $property['property_status_id_field'],
			'property_status_details' => $property->property_status,
			'price'                  => $property['price_field'],
			'oldPrice'               => $property['old_price_field'],
			'premium'                => $property['premium_field'],
			'rentPeriod'             => $property['rent_period_field'],
			'dateAvailable'          => $property['date_available_field'],
			'priceOnRequest'         => $property['por_field'],
			'description'            => $property['description_field'],
			'title'                  => $property['title_field'],
			'longDescription'        => $property['long_description_field'],
			'specifications'         => $property['specifications_field'],
			'itemsIncludedInPrice'   => $property['items_included_in_price_field'],
			'soleAgents'             => (bool) $property['sole_agents_field'],
			'propertyBlock'          => $property->propertyBlock,
			'bedrooms'               => $property['bedrooms_field'],
			'bathrooms'              => $property['bathrooms_field'],
			'garage'				 => $garage_value ?? null,
			'contactDetails'         => $property['contact_details_field'],
			'isPropertyOfTheMonth'   => $property['is_property_of_the_month_field'],
			'isFeatured'			 => $property['is_featured_field'],
			'isHotProperty'          => $property['is_hot_property_field'],
			'dateOnMarket'           => $property['date_on_market_field'],
			'dateOffMarket'          => $property['date_off_market_field'],
			'datePriceReduced'       => $property['date_price_reduced_field'],
			'virtualTourUrl'         => $property['virtual_tour_url_field'],
			'showOn3rdPartySites'    => $property['show_on_3rd_party_sites_field'],
			'pricesStartingFrom'     => $property['prices_starting_from_field'],
			'hotPropertyTitle'       => $property['hot_property_title_field'],
			'area'                   => $property['area_field'],
			'plot_area'              => $property['plot_area_field'],
			'external_area'          => $property['external_area_field'],
			'internal_area'          => $property['internal_area_field'],
			'weight'                 => $property['weight_field'],
			'features'               => $this->featureTransformer->transformCollection($property->features->sortBy('sort_order')->toArray()),
			'files'                  => $files_transformed,
			'consultant'             => $consultant_transformed,
			'latitude'               => $property['latitude_field'],
			'longitude'              => $property['longitude_field'],
			'show_in_searches'         => $property['show_in_searches'],
			'three_d_walk_through'      => $property['three_d_walk_through'],
			'is_managed_property'      => $property['is_managed_property'],
			'project'      			 => $property['project_id'],
			'project_details'        => $property->project,
			'created_at'             => $property['orig_created_at'],
			'updated_at'             => $property['updated_at']
		];
	}
}
