<?php

namespace App\Components\Repositories\Impl;

use App\Components\Passive\Utilities;
use App\Components\Repositories\IPropertyRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use App\Scopes\PropertyScope;
use App\File;
use App\Models\Country;

class PropertyRepository implements IPropertyRepository
{
	public function createPropertyWithRefOnly($reference)
	{
		return Property::updateOrCreate([
			'reference' => $reference
		]);
	}

	public function getBySlug($slug)
	{
		return Property::where('slug', $slug)->first();
	}

	public function getPropertyByRef($reference)
	{
		return Property::where('property_ref_field', $reference)->first();
	}

	public function getPropertyOldId($id)
	{
		return Property::where('old_id', (string) $id)->first();
	}

	public function getPropertyImageByRef($reference)
	{
		return File::where('property_id', $reference)->first();
	}

	public function getPropertyBasicDataByRef($reference)
	{
		$property = Property::where('property_ref_field', $reference)->first();
		return [
			"id" => $property->id,
			"old_id" => $property->old_id,
			"reference" => $property->property_ref_field,
		];
	}

	public function detachUser($user, $property)
	{
		$property = $this->getPropertyByRef($property);

		$property->user_favorite()->detach($user);
	}

	public function createProperty($data) //old property api5
	{
		return Property::create($data);
	}

	public function updateProperty($reference, $data) //old property api5
	{
		return Property::where('id', $reference)->update($data);
	}

	public function updatePropertyByRef($reference, $data) //old property api5
	{
		return Property::where('property_ref_field', $reference)->update($data);
	}

	public function updatePropertyByOldId($id, $data)
	{
		return Property::where('old_id', (string) $id)->update($data);
	}

	public function getPropertyByRefID($reference) //old property api5
	{
		return Property::where('id', $reference)->first();
	}

	public function getPropertyByNodeID($node_id)
	{
		return Property::where('old_id', $node_id)->first();
	}

	public function getAllProperties() //old property api5
	{
		return Property::all()->pluck('old_id');
	}

	public function search($parameter = [], $limit = 10, $sort_order = 'latest')
	{
		$property = Property::where('properties.status', 1)->selectRaw(
			"DISTINCT property_ref_field as ref, 
			properties.id as webRef,   
			slug, 
			properties.meta_data,
			market_status_field as marketStatus,
			market_type_field  as marketType,
			commercial_field,
			locality_id_field,
			locality_name, 
			locality.country_id,
			locality.region_id,

			region_field,
			regions.old_id as region_old_id,
			regions.description as region_description,
			regions.sequence_no as region_sequence_no,

			property_type_id_field,
			propertytype.description as propertytype_description,

			property_status_id_field,
			property_status.description as property_status_description,

			price_field as price,
			rent_period_field as rentPeriod,
			por_field as priceOnRequest, 
			sole_agents_field as soleAgents,
			bedrooms_field as bedrooms,
			bathrooms_field as bathrooms,
			region_field,
			area_field as area,

			consultant_id,

			consultants.full_name_field,
			consultants.image_file_name_field,
			consultants.contact_number_field,
			consultants.email_field,
			consultants.image_name_field,
			consultants.orig_consultant_image_src,
			consultants.url_field,
			consultants.image_status_field,
			properties.created_at,
			properties.orig_created_at,
			date_available_field"
		)
			->leftjoin('consultants', 'consultants.id', '=', 'properties.consultant_id')
			->leftjoin('propertytype', 'propertytype.id', '=', 'properties.property_type_id_field')
			->leftjoin('property_status', 'property_status.id', '=', 'properties.property_status_id_field')
			->leftjoin('locality', 'locality.id', '=', 'properties.locality_id_field')
			->leftjoin('regions', 'regions.id', '=', 'properties.region_field');

		$sort_filters = [];
		foreach ($parameter as $key => $value) {
			if (isset($value) && $value !== '') {
				switch ($key) {

					case 'consultant_id':
						$property->whereIn($key, $value);
						break;
					case 'property_status_id_field':
						$selected_property_status = array_map('trim', explode('-', $value));
						$property->whereIn($key, $selected_property_status);
						break;
					case 'property_type_id_field':

						$property->whereIn($key, $value);
						break;

					case 'garage':
						$garage = DB::table('features')->select('id')->where('feature_value', 'Car Space');
						$property_with_specific_garage_num = DB::table('feature_property')->select('property_id')->whereIn('feature_id', $garage)->where('feature_value', '>=', $value);
						$property->whereIn('properties.id', $property_with_specific_garage_num);
						break;

					case 'bedrooms_field':
						$property->where('bedrooms_field', '>=', $value);
						break;

					case 'bathrooms_field':
						$property->where('bathrooms_field', '>=', $value);
						break;

					case 'features':
						$feature_ids = array_map('trim', explode('-', $value));
						$property_filtered = DB::table('feature_property')->select('property_id')->whereIn('feature_id', $feature_ids)->whereNotIn('feature_value', ['No', 'no']);
						$property->whereIn('properties.id', $property_filtered);
						break;

					case 'priceFrom':
						$property->where('price_field', '>=', $value);
						break;

					case 'priceTo':
						$property->where('price_field', '<=', $value);
						break;

					case 'areaFrom':
						$property->where('area_field', '>=', $value);
						break;

					case 'areaTo':
						$property->where('area_field', '<=', $value);
						break;

					case 'region_field':
						$selected_regions = array_map('trim', explode('-', $value));
						$property->whereIn($key, $selected_regions);
						break;

					case 'locality_id_field':
						$selected_localities = array_map('trim', explode('-', $value));
						$property->whereIn($key, $selected_localities);
						break;

					case 'sole_agents_field':
						if ('true' == $value)
							$value = 1;
						else
							$value = 0;
						$property->where($key, $value);
						break;

					case 'property_ref_field':
						$property->where($key, 'like', '%' . $value);
						break;

					case 'property_refs':
						$refs =  array_map("trim", explode(',', $value));
						$sort_filters['refs-request'] = $refs;
						$property->whereIn('property_ref_field', $refs);
						break;

					case 'availableFrom':
						$property->where('date_available_field', '>=', $value);
						break;

					case 'availableUntil':
						$property->where('date_available_field', '<=', $value);
						break;

					case 'country_code':
						$country = Country::select('id')->where('code', $value)->first();
						if (isset($country) && $country) {
							Log::debug("country:" . $country->id);
							$property->whereHas('locality', function ($query) use ($country) {
								$query->where('country_id', $country->id);
							});
							// $property->where('locality.country_id',$country->id);
						} else {
							Log::debug('country not found');
						}
						break;

					default:
						if (is_numeric($value)) {
							$property->where($key, $value);
						} else {
							$property->where($key, 'like', '%' . $value . '%');
						}
				}
			}
		}
		$property->with(['files' => function ($query) {
			$query->where("file_type_field", "=", "MainImage");
		}])->first();


		// $property->where(function ($query) {
		// 	$query->whereNotIn('market_status_field', ["Sold", "Discreet Offline"])->orWhereNull("market_status_field");
		// })->where('properties.status', 1);

		$this->sortRaw($sort_order, $property, $sort_filters);

		return $property->paginate($limit);
	}

	public function addPriorityValue($sort_order, $properties, $parameter)
	{
		foreach ($properties	as $property) {
			$prio_number = 10;
			foreach ($parameter as $key => $value) {

				if (isset($value) && $value) {
					switch ($key) {
						case 'property_type_id_field':
							$selected_property_types = array_map('trim', explode('-', $value));
							if (in_array($property->property_type_id_field, $selected_property_types)) {
								$prio_number += 50;
							}
							break;

						case 'features':
							$feature_ids = array_map('trim', explode('-', $value));
							$query = DB::table('feature_property')->select('feature_id')->where('property_id', $property->id)->whereIn('feature_id', $feature_ids)->whereNotIn('feature_value', ['No', 'no']);
							$prio_number += $query->count();
							break;
					}
				}
			}
			$property->priority_number = $prio_number;
		}
		return $properties;
	}

	public function sortRaw($order, &$properties = null, $filters = [])
	{
		if (is_null($properties)) {
			switch ($order) {
				case 'price-asc':
					return Property::priceASC();
				case 'price-desc':
					return Property::priceDESC();
				case 'earliest':
					return Property::createdAtASC();
				default: // latest
					return Property::createdAtDESC();
			}
		} else {
			switch ($order) {
				case 'price-asc':
					return $properties->orderBy('price', 'asc');
				case 'price-desc':
					return $properties->orderBy('price', 'desc');
				case 'recommended':
					//Recommended [should list sole agency listings first]
					return $properties->orderBy('soleAgents', 'desc')->orderBy('properties.orig_created_at', 'desc');
				case 'recently-updated':
					return $properties->orderBy('properties.created_at', 'desc');
				case 'earliest':
					return $properties->orderBy('properties.orig_created_at', 'asc');
				case 'refs-request':
					if (!empty($filters['refs-request']) && count($filters['refs-request']) > 0) {
						$quotedOrder = implode(',', array_map(function ($id) {
							return DB::getPdo()->quote($id);
						}, $filters['refs-request']));
						return $properties->orderByRaw("FIELD(property_ref_field, " . $quotedOrder . ")");
					}
				case 'availability':
					return $properties->orderBy('properties.date_available_field', 'asc');
				default: // latest
					return $properties->orderBy('properties.orig_created_at', 'desc');
			}
		}
	}

	public function sort($order, &$properties = null)
	{
		if (is_null($properties)) {
			switch ($order) {
				case 'price-asc':
					return Property::priceASC();
				case 'price-desc':
					return Property::priceDESC();
				case 'earliest':
					return Property::createdAtASC();
				default: // latest
					return Property::createdAtDESC();
			}
		} else {
			switch ($order) {
				case 'price-asc':
					return $properties->priceASC();
				case 'price-desc':
					return $properties->priceDESC();
				case 'earliest':
					return $properties->createdAtASC();
				default: // latest
					return $properties->createdAtDESC();
			}
		}
	}

	public function reducedPrice($limit = 10, $days_limit = 7)
	{
		$properties = Property::whereRaw("price_field < old_price_field")
			->whereNotNull('date_price_reduced_field')
			->where('date_price_reduced_field', ">=", Carbon::now()->subDays($days_limit));

		$properties->where(function ($query) {
			$query->whereNotIn('market_status_field', ["Sold", "Discreet Offline"])->orWhereNull("market_status_field");
		})->where('status', 1);

		return $properties->paginate($limit);
	}

	public function increasedPrice($limit = 10, $days_limit = 7)
	{
		$properties =  Property::whereRaw("price_field > old_price_field")
			->whereNotNull('date_price_reduced_field')
			->where('date_price_reduced_field', ">=", Carbon::now()->subDays($days_limit));

		$properties->where(function ($query) {
			$query->whereNotIn('market_status_field', ["Sold", "Discreet Offline"])->orWhereNull("market_status_field");
		})->where('status', 1);

		return $properties->paginate($limit);
	}

	public function getAllPropertiesNolimit() //old property api5
	{
		return Property::orderBy('orig_created_at', 'DESC')->get();
	}
}
