<?php

namespace App\Models;

use App\Locality;
use Carbon\Carbon;
use App\Models\File;
use App\Models\User;
use App\Scopes\PropertyScope;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use App\Observers\PropertyObserver;
use App\Models\PropertyType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PropertyObserver::class])]
class Property extends Model
{
	use HasFactory;
	protected $keyType = 'string';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'old_id',
		'property_ref_field',
		'market_status_field',
		'expiry_date_time',
		'market_type_field',
		'commercial_field',
		'locality_id_field',
		'property_type_id_field',
		'property_status_id_field',
		'price_field',
		'old_price_field',
		'premium_field',
		'rent_period_field',
		'date_available_field',
		'por_field',
		'description_field',
		'title_field',
		'long_description_field',
		'specifications_field',
		'items_included_in_price_field',
		'sole_agents_field',
		'property_block_id_field',
		'bedrooms_field',
		'bathrooms_field',
		'contact_details_field',
		'is_property_of_the_month_field',
		'is_featured_field',
		'is_hot_property_field',
		'date_on_market_field',
		'date_off_market_field',
		'date_price_reduced_field',
		'virtual_tour_url_field',
		'show_on_3rd_party_sites_field',
		'prices_starting_from_field',
		'hot_property_title_field',
		'area_field',
		'weight_field',
		'consultant_id',
		'latitude_field',
		'longitude_field',
		'show_in_searches',
		'is_managed_property',
		'three_d_walk_through',
		'status',
		'user_id',
		'region_field',
		'project_id',
		'priority_number',
		'orig_created_at',
		'slug',
		'external_area_field',
		'internal_area_field',
		'plot_area_field',
		'to_synch',
		'data',
		'meta_data'
	];

	protected $hidden = [
		'created_at',
		'updated_at',
	];

	protected $casts = [
		'meta_data' => 'array', // Cast JSON to array
	];

	protected $dates = [
		'expiry_date_time',
		'date_available_field',
		'date_on_market_field',
		'date_off_market_field',
		'date_price_reduced_field'
	];


	public function scopeActive($query)
	{
		return $query->where('status', 1);
	}
	public function get_id()
	{
		return $this->id;
	}

	public function user_favorite()
	{
		return $this->belongsToMany(User::class, 'user_property', 'property_id', 'user_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function features()
	{
		return $this->belongsToMany('App\Feature')->withPivot('feature_value');
	}

	public function features_filter()
	{
		return $this->belongsToMany('App\Feature');
	}

	public function consultant()
	{
		return $this->belongsTo('App\Consultant');
	}

	public function property_type()
	{
		return $this->belongsTo('App\Models\PropertyType', 'property_type_id_field', 'id');
	}
	public function propertyBlock()
	{
		return $this->belongsTo('App\PropertyBlock', 'property_block_id_field');
	}

	public function property_status()
	{
		return $this->belongsTo('App\PropertyStatus', 'property_status_id_field', 'id');
	}

	public function files(): HasMany
	{
		return $this->hasMany(File::class, 'property_id', 'id');
	}

	public function scopePriceASC($query)
	{
		return $query->orderBy('price_field', 'ASC');
	}

	public function scopePriceDESC($query)
	{
		return $query->orderBy('price_field', 'DESC');
	}

	public function scopePropertyTypeASC($query)
	{
		return $query->orderBy('property_type_id_field', 'ASC');
	}

	public function scopePropertyTypeDESC($query)
	{
		return $query->orderBy('property_type_id_field', 'DESC');
	}

	public function scopeLocalityASC($query)
	{
		return $query->orderBy('locality_id_field');
	}

	public function scopeWeightDESC($query)
	{
		return $query->orderBy('weight_field', 'DESC');
	}

	public function scopeTitleASC($query)
	{
		return $query->orderBy('title_field', 'ASC');
	}

	public function scopeAvailableFromASC($query)
	{
		return $query->orderBy('date_available_field', 'ASC');
	}

	public function scopeCreatedAtDESC($query)
	{
		return $query->orderBy('orig_created_at', 'DESC');
	}

	public function scopeCreatedAtASC($query)
	{
		return $query->orderBy('orig_created_at', 'ASC');
	}
	public function scopeRandom($query)
	{
		return $query->orderBy(DB::raw('RAND()'));
	}

	public function scopeIsPublic($query)
	{
		return $query->where('status', 1);
	}

	public function locality()
	{
		return $this->belongsTo(Locality::class, 'locality_id_field');
	}

	public function type()
	{
		return $this->belongsTo(PropertyType::class, 'property_type_id_field');
	}

	public function region(): BelongsTo
	{
		return $this->belongsTo(Region::class, 'region_field');
	}

	public function project(): BelongsTo
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}
}
