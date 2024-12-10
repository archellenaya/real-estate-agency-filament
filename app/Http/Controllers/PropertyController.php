<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Consultant;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\DTO\PropertyDTO;
use App\Models\Property;
use App\Constants\Http\StatusCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Http\Requests\SearchRequest;
use App\Models\DTO\PropertyInnerDTO;
use Illuminate\Support\Facades\Http;
use App\Components\Passive\Utilities;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Cache;
use App\Niu\Transformers\FileTransformer;
use Illuminate\Support\Facades\Validator;
use App\Models\PropertyTypeGroup;
use App\Niu\Transformers\FeatureTransformer;
use App\Components\Services\ILocalityService;
use App\Components\Services\IPropertyService;
use App\Niu\Transformers\PropertyTransformer;
use App\Components\Services\ICloudflareService;
use App\Niu\Transformers\PropertySearchTransformer;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Repositories\Impl\LocalityRepository;
use App\Components\Services\ISeoPropertyCategoryService;

class PropertyController extends ApiController
{
    private $_propertyService;
    private $_localityService;
    protected $_propertyTransformer;
    protected $_propertySearchTransformer;
    protected $_featureTransformer;
    protected $_fileTransformer;
    protected $_cfService;
    protected $seoPropertyCategoryService;
    protected $expiration_time = 2628288;
    protected $_default_options;


    public function __construct(
        PropertyTransformer $propertyTransformer,
        FeatureTransformer $featureTransformer,
        FileTransformer $fileTransformer,
        PropertySearchTransformer $propertySearchTransformer,
        IPropertyService $propertyService,
        ILocalityService $localityService,
        ICloudflareService $cfService,
        ISeoPropertyCategoryService $seoPropertyCategoryService,
    ) {
        $this->_propertySearchTransformer = $propertySearchTransformer;
        $this->_featureTransformer = $featureTransformer;
        $this->_fileTransformer = $fileTransformer;
        $this->_propertyTransformer = $propertyTransformer;
        $this->_propertyService = $propertyService;
        $this->_localityService = $localityService;
        $this->_cfService = $cfService;
        $this->seoPropertyCategoryService = $seoPropertyCategoryService;
        $defaults = explode(',', config('app.default_options'));
        $this->_default_options = array_map(function ($item) {
            return strtolower(trim($item));
        }, $defaults);
    }

    private function getValuePairEquivalent($key, $val)
    {
        switch ($key) {
            case 'bathrooms':
            case 'bedrooms':
            case 'localities':
            case 'regions':
                return array(
                    $key => $val
                );
            case 'propertytype':
                return array(
                    'property-type' => $val
                );
            case 'forSale':
                return array(
                    'market-type' => 'Sale'
                );
            case 'toLet':
                return array(
                    'market-type' => 'Rent'
                );
        }
    }

    public function seoPropertySearch(Request $request, $slug)
    {
        $paramString = $this->seoPropertyCategoryService->getQueryVarsBySlug($slug);

        return redirect("/" . tenant('id') . "/api/v1/properties/search?" . $paramString);
    }

    public function seoPropertySlug(Request $request, $slug)
    {
        $property =  Property::where('slug', $slug)->paginate(10 ?? 1);
        $aid  = $request->get('aid');

        if (isset($aid)) { //agent_code_id
            $consultant = Consultant::where('agent_code', $aid)->first();
            if (empty($consultant)) {
                $consultant = Consultant::find($aid);
            }
            if (!empty($consultant)) {

                $consultant_aid = [
                    'id'             => $consultant->id ?? null,
                    'old_id'         => $consultant->old_id ?? null,
                    'fullNameField'   => $consultant->full_name_field ?? null,
                    'imageFilenameField' => $consultant->image_file_name_field ?? 'default.webp',
                    'imageNameField'       => $consultant->image_name_field ?? 'default.webp',
                    'branchId'      => $consultant->branch_id_field ?? null,
                    'branch_name' => $consultant->getBranchName() ?? null,
                    'descriptionField'    => $consultant->description_field ?? null,
                    'designationField'    => $consultant->designation_field ?? null,
                    'whatsappNumberField'    => $consultant->whatsapp_number_field ?? null,
                    'contactNumberField' => $consultant->contact_number_field ?? null,
                    'officePhoneField'   => $consultant->office_phone_field ?? null,
                    'emailField'          => $consultant->email_field ?? null,
                    'isAvailable'    => $consultant->is_available ?? null,
                    'sourcePhotoUrl'  => $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                    'image_status_field' => $consultant->image_status_field ?? null,
                    'url_field' => $consultant->url_field ?? $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                    'listingCounts' => $consultant->properties()->count()
                ];
            }
        }

        $propertyTransformed = $this->_propertyTransformer->transformCollection($property->all());
        $new_propertyTransformed = [];
        foreach ($propertyTransformed as $property) {
            $formatted_features = [];
            foreach ($property['features'] as $feature) {
                $formatted_features[$feature["id"]] = $feature;
            }
            $property['formatted_features'] = $formatted_features;
            if (!empty($consultant_aid)) {
                $property['consultant'] = $consultant_aid;
            }
            $new_propertyTransformed[] = $property;
        }
        return $new_propertyTransformed;
    }

    public function search(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1',
            'property-type' => ['nullable', 'regex:/^[1-9]\d*(-[1-9]\d*)*$/'],
            'property-subtype' => ['nullable', 'regex:/^[1-9]\d*(-[1-9]\d*)*$/'],
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $reference        = $request->get('ref');
        $references       = $request->get('refs');
        $aid              = $request->get('aid');
        $consultant_ids   = $request->get('consultant');
        $marketStatus     = $request->get('market-status');
        $propertyTypes    = $request->get('property-type');
        $propertySubTypes = $request->get('property-subtype');
        $not_by_group     = $request->get('not-by-group');
        $priceFrom        = $request->get('price-from');
        $priceTo          = $request->get('price-to');
        $regions          = $request->get('regions');
        $localities       = $request->get('localities');
        $marketType       = $request->get('market-type');
        $soleAgent        = $request->get('sole-agent');
        $property_status   = $request->get('property-status');
        $bedrooms         = $request->get('bedrooms');
        $bathrooms        = $request->get('bathrooms');
        $areaFrom         = $request->get('area-from');
        $areaTo           = $request->get('area-to');
        $garage           = $request->get('garage');
        $features         = $request->get('features');
        $country_code     = $request->get('country-code');
        $is_commercial    = $request->get('is-commercial');
        $availableFrom    = $request->get('available-from');
        $availableUntil   = $request->get('available-until');
        $sort_order       = $request->get('sort') ?? 'latest';
        $limit            = $request->get('limit') ?? 10;
        // $not_by_group = false or 0  >>$propertyTypes = Property Group
        // $not_by_group = true or 1  >>$propertyTypes = Property SubType 
        if (empty($propertySubTypes)) {
            if (empty($not_by_group) && !empty($propertyTypes)) {
                $selected_property_main_types = array_map('trim', explode('-', $propertyTypes));
                $propertyTypes = DB::table('propertytype')->select('id')->whereIn('property_type_groupId', $selected_property_main_types)->pluck('id');
            } else {
                if (!empty($propertyTypes)) {
                    $propertyTypes = array_map('trim', explode('-', $propertyTypes));
                }
            }
        } else {
            $propertyTypes = array_map('trim', explode('-', $propertySubTypes));
        }

        $agent_ids = null;
        if (!empty($consultant_ids)) {
            $selected_consultant_ids = array_map('trim', explode('-', $consultant_ids));
            $consultants = Consultant::whereIn('id', $selected_consultant_ids)->get() ?? Consultant::whereIn('agent_code', $selected_consultant_ids)->first();
            if ($consultants) {
                $agent_ids = array_map('trim', explode('-', $consultant_ids));
            }
        }

        $parameter = [
            'consultant_id'             => $agent_ids ?? null,
            'property_ref_field'        => $reference ?? null,
            'property_refs'             => $references ?? null,
            'market_status_field'       => $marketStatus ?? null,
            'property_type_id_field'    => $propertyTypes ?? null,
            'priceFrom'                 => $priceFrom ?? null,
            'priceTo'                   => $priceTo ?? null,
            'region_field'              => $regions ?? null,
            'locality_id_field'         => $localities ?? null,
            'market_type_field'         => $marketType ?? null,
            'sole_agents_field'         => $soleAgent ?? null,
            'bedrooms_field'            => $bedrooms ?? null,
            'bathrooms_field'           => $bathrooms ?? null,
            'property_status_id_field'  => $property_status ?? null,
            'areaFrom'                  => $areaFrom ?? null,
            'areaTo'                    => $areaTo ?? null,
            'garage'                    => $garage ?? null,
            'features'                  => $features ?? null,
            'commercial_field'          => $is_commercial ?? null,
            'availableFrom'             => $availableFrom ?? null,
            'availableUntil'            => $availableUntil ?? null,
            'country_code'              => $country_code ?? null,
        ];

        try {

            $properties = $this->_propertyService->search($parameter, $limit, $sort_order);
            // $properties_ids = $properties->pluck('webRef');

        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        $property_collections = [];
        $locality = new LocalityRepository;
        // $garage = DB::table('features')->select('id')->where('feature_value', 'Car Space')->first();

        // if(isset($garage)) { 
        //     $garage_collection_db = DB::table('feature_property')->select('feature_value','property_id')->where('feature_id', $garage->id)->whereIn('property_id', $properties_ids)->get();
        //     $garage_collection = array_map(function($collection)  
        //     {  
        //         // if(!empty($collection))
        //         echo print_r($collection, true);
        //         // return [$collection['property_id'] => $collection['feature_value']];
        //     } ,(array)$garage_collection_db);
        // }

        $agent = array();
        if (isset($aid)) { //agent_code_id
            $consultant = Consultant::where('agent_code', $aid)->first();
            if (empty($consultant)) {
                $consultant = Consultant::find($aid);
            }
            if (!empty($consultant)) {
                //
                $agent = [
                    "id" => $consultant->id ?? null,
                    "fullNameField" => $consultant->full_name_field ?? null,
                    "contactNumberField" => $consultant->contact_number_field ?? null,
                    "emailField" => $consultant->email_field ?? null,
                    "imageFilenameField" => $consultant->image_file_name_field ?? 'default.webp',
                    "imageNameField" => $consultant->image_name_field ?? 'default.webp',
                    "origConsultantImageSrc" => $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                    "image_status_field" => $consultant->image_status_field ?? null,
                    "url_field" =>  $consultant->url_field ?? $consultant->orig_consultant_image_src ??  config('url.consultant_thumbnail'),

                ];
            }
        }

        foreach ($properties as $property) {
            if (empty($consultant)) {
                $agent = [
                    "id" => $property->consultant_id ?? null,
                    "fullNameField" => $property->full_name_field ?? null,
                    "contactNumberField" => $property->contact_number_field ?? null,
                    "emailField" => $property->email_field ?? null,
                    "imageFilenameField" => $property->image_file_name_field ?? 'default.webp',
                    "imageNameField" => $property->image_name_field ?? 'default.webp',
                    "origConsultantImageSrc" => $property->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                    "image_status_field" => $property->image_status_field ?? null,
                    "url_field" =>  $property->url_field ?? $property->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                ];
            }

            $thumbnail = null;
            if (isset($property->webRef)) {
                $file = File::where('property_id', $property->webRef)->where('file_type_field', 'MainImage')->orderBy('created_at', 'desc')->first();

                $thumbnail = [
                    "file_name" => $file->file_name_field ?? 'default.webp',
                    "file_type" => $file->file_type_field ?? 'MainImage',
                    "orig_path" => $file->orig_image_src ?? config('url.property_thumbnail'),
                    'url_field' => $file->url_field ?? $file->orig_image_src ?? config('url.property_thumbnail'),
                    'image_status_field' => $file->image_status_field ?? null,
                ];
            }

            $property_collections[] = new PropertyDTO(
                $property->ref,
                $property->meta_data['tags'] ?? [],
                $property->slug,
                $property->area,
                !empty($thumbnail) ? array($thumbnail) : array(),
                $property->price,
                $property->rentPeriod,
                $property->webRef,
                null,
                // $garage_value->feature_value ?? null,
                $property->locality_id_field,
                $property->bedrooms,
                $property->bathrooms,
                (bool) $property->soleAgents,
                $agent,
                $property->marketType,
                $property->marketStatus,
                [
                    "id" => $property->region_field,
                    "old_id" => $property->region_old_id,
                    "description" => $property->region_description,
                    "sequence_no" => $property->region_sequence_no,
                ],
                $property->priceOnRequest,
                [
                    "id" => $property->locality_id_field,
                    "locality_name" => $property->locality_name,
                    "region" => $property->region_description,
                ],
                [
                    "id" =>  $property->property_type_id_field,
                    "description" => $property->propertytype_description,
                ],
                [
                    "id" =>  $property->property_status_id_field,
                    "description" => $property->property_status_description,
                ],
                $property->date_available_field
            );
        }

        return $this->respond([
            'paginator' => [
                'total_count'  => $properties->total(),
                'total_pages'  => $properties->lastPage(),
                'current_page' => $properties->currentPage(),
                'limit'        => $properties->perPage(),
            ],
            'data' => $property_collections
        ]);
        // return 1;
    }

    public function regions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $names = $request->input('name');
        $regions = $this->_localityService->getRegions($names);

        $region_collections = [];
        foreach ($regions as $region) {
            if (in_array(strtolower($region['description']), $this->_default_options)) continue;

            $region_collections[$region['id']] = $region['description'];
        }

        return $this->setJsonDataResponse($region_collections);
    }

    public function priceUpdate(Request $request)
    {
        $limit = $request->get('limit') ?? '10';
        $event = $request->get('event') ?? 'reduced'; // reduced || increased
        $days_limit = $request->get('days') ?? '7';

        try {
            if (strcasecmp($event, 'reduced') == 0) {
                $properties = $this->_propertyService->reducedPrice($limit,  $days_limit);
            } else if (strcasecmp($event, 'increased') == 0) {
                $properties = $this->_propertyService->increasedPrice($limit,  $days_limit);
            } else {
                return $this->setJsonMessageResponse(ProcessExceptionMessage::UNKNOWN_PRICE_EVENT, StatusCode::HTTP_BAD_REQUEST);
            }
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->respondWithPagination($properties, [
            'data' => $this->_propertyTransformer->transformCollection($properties->all())
        ]);
    }

    public function consultantProperty(Request $request, $consultantId)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $limit  = $request->get('limit');

        $marketType = $request->get('market-type');

        $consultant = Consultant::where('agent_code', $consultantId)->first();
        if (empty($consultant)) {
            $consultant = Consultant::find($consultantId);
            if (empty($consultant)) {
                return $this->respondNotFound('Consultant does not exist');
            }
        }
        // $consultant_properties = Property::where('consultant_id', $consultant->id); 
        if (isset($marketType) && $marketType) {
            $consultant_properties = Property::isPublic()
                ->with('region', 'locality', 'property_status', 'property_type', 'consultant', 'files')
                ->where('consultant_id', $consultant->id)->where('market_type_field', $marketType)->orderBy('orig_created_at', 'DESC')->paginate($limit ?? 10);
            // $consultant_properties = Property::where('consultant_id', $consultant->id)->orderBy('orig_created_at', 'DESC')->paginate( $limit ?? 10 );
        } else {
            $consultant_properties = Property::isPublic()
                ->with('region', 'locality', 'property_status', 'property_type', 'consultant', 'files')
                ->where('consultant_id', $consultant->id)->orderBy('orig_created_at', 'DESC')->paginate($limit ?? 10);
        }

        $property_collections = [];

        foreach ($consultant_properties as $property) {

            $agent = [
                "id" => $property->consultant->id ?? null,
                "fullNameField" =>  $property->consultant->full_name_field ?? null,
                "contactNumberField" => $property->consultant->contact_number_field ?? null,
                "emailField" =>  $property->consultant->email_field ?? null,
                "imageFilenameField" =>  $property->consultant->image_file_name_field ?? 'default.webp',
                "imageNameField" =>  $property->consultant->image_name_field ?? 'default.webp',
                "origConsultantImageSrc" => $property->consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail') ?: null,
                'url_field' => $property->consultant->url_field ?? $property->consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                'image_status_field' => $consultant['image_status_field'] ?? null,
            ];

            $thumbnail = null;
            if (isset($property->property_ref_field)) {
                $file = $property->files->where('file_type_field', 'MainImage')->sortByDesc('created_at')->first();

                $thumbnail = [
                    "file_name" => $file->file_name_field ?? 'default.webp',
                    "file_type" => $file->file_type_field ?? 'MainImage',
                    "orig_path" => $file->orig_image_src ?? config('url.property_thumbnail'),
                    'url_field' => $file->url_field ?? $file->orig_image_src ?? config('url.property_thumbnail'),
                    'image_status_field' => $file->image_status_field ?? null,
                ];
            }

            $property_collections[] = new PropertyDTO(
                $property->property_ref_field,
                $property->meta_data['tags'] ?? [],
                $property->slug,
                $property->area_field,
                !empty($thumbnail) ? array($thumbnail) : array(),
                $property->price_field,
                $property->rent_period_field,
                $property->id,
                null,
                // $garage_value->feature_value ?? null,
                $property->locality_id_field,
                $property->bedrooms_field,
                $property->bathrooms_field,
                (bool) $property->sole_agents_field,
                $agent,
                $property->market_type_field,
                $property->market_status_field,
                $property->region,
                $property->por_field,
                [
                    "id" => $property->locality_id_field,
                    "locality_name" => $property->locality?->locality_name ?? null,
                    "region" => $property->locality?->region ?? null,
                ],
                [
                    "id" =>  $property->property_type_id_field,
                    "description" => $property->property_type->description ?? null,
                ],
                [
                    "id" =>  $property->property_status_id_field,
                    "description" => $property->property_status->description ?? null,
                ],
                $property->date_available_field
            );
        }

        return $this->respond([
            'paginator' => [
                'total_count'  => $consultant_properties->total(),
                'total_pages'  => $consultant_properties->lastPage(),
                'current_page' => $consultant_properties->currentPage(),
                'limit'        => $consultant_properties->perPage(),
            ],
            'data' => $property_collections
        ]);
        // return $this->respondWithPagination( $consultant_properties, [
        // 	'data' => $this->_propertyTransformer->transformCollection( $consultant_properties->all() )
        // ]);

    }

    public function cacheClear(Request $request)
    {
        $key = $request->get('key') ?? null;
        $tags = $request->get('tags') ?? null;
        if (isset($key)) {
            Cache::forget($key);
            return $this->setJsonMessageResponse($key . " cache-key successfully deleted", StatusCode::HTTP_OK);
        } else if (isset($tags)) {
            $tags = array_map('trim', explode(',', $tags));
            Cache::tags($tags)->flush();
            return $this->setJsonMessageResponse("cache-tags successfully deleted", StatusCode::HTTP_OK);
        } else {
            Cache::flush();
            return $this->setJsonMessageResponse("all cached cleared successfully", StatusCode::HTTP_OK);
        }
    }

    public function clearPropertyCache(Request $request, $reference)
    {
        if (empty($reference)) {
            return $this->setJsonMessageResponse("Property reference required.", StatusCode::HTTP_BAD_REQUEST);
        }
        return $this->_cfService->purgePropertyByReference($reference);
    }

    public function getSEOPropertyCategories()
    {
        $seoPropertyCategories = $this->seoPropertyCategoryService->getAllSlug();
        return $this->respond($seoPropertyCategories);
    }

    public function getSEOPropertyCategory($slug)
    {
        $seoPropertyCategory = $this->seoPropertyCategoryService->getBySlug($slug);
        return $this->respond($seoPropertyCategory);
    }

    public function getSEOPropertySlugs()
    {
        $seoPropertySlugs = $this->seoPropertyCategoryService->getAllInnerPropertySlug();
        return $this->respond($seoPropertySlugs);
    }

    public function getInnerPropertySlugs(Request $request)
    {
        $limit  = $request->get('limit');

        $tempSeoSlugs = Property::select('slug', 'updated_at')->whereNotNull('slug')->where('status', 1)->withCount('files')->orderBy('updated_at', 'desc')->paginate($limit);
        $seoSlugs = [];
        foreach ($tempSeoSlugs as $seo) {
            $seoSlugs[] = [
                'slug' => $seo->slug,
                'images' => $seo->files_count,
                'last_modified' => $seo->updated_at,
            ];
        }
        return $this->respond([
            'paginator' => [
                'total_count'  => $tempSeoSlugs->total(),
                'total_pages'  => $tempSeoSlugs->lastPage(),
                'current_page' => $tempSeoSlugs->currentPage(),
                'limit'        => $tempSeoSlugs->perPage(),
            ],
            'data' => $seoSlugs
        ]);
    }

    public function seoPropertyMetatags(Request $request)
    {
        $reference  = $request->get('ref');

        try {

            $property =  $this->_propertyService->getPropertyByRef($reference);
            if (empty($property)) {
                return $this->setJsonMessageResponse(ProcessExceptionMessage::PROPERTY_NOT_EXIST, StatusCode::HTTP_NOT_FOUND);
            }
            $image_main =  $this->_propertyService->getPropertyImageByRef($reference);
            return [
                "title" => !empty($property->title_field) ? $property->title_field : Utilities::generate_property_inner_title($property),
                "description" => strip_tags($property->long_description_field),
                "image" => isset($image_main) ? $image_main->file_name_field : 'default.png',
            ];
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
    }

    public function checkProperty(Request $request)
    {
        $request->validate([
            'refs' => 'required|string', // Sanitize using strip_tags to remove HTML tags 
        ]);

        $reference  =  $request->get('refs');
        // $reference  = trim($reference); 
        $reference  = strip_tags($reference);
        $reference  = htmlspecialchars($reference);

        $references = explode(",", $reference);
        $references = array_map('trim', $references);
        $data = [];

        foreach ($references as $ref) {
            $data[] = [
                'id' => $ref,
                'status' => Property::where('property_ref_field', $ref)->exists() ? "Yes" : "No"
            ];
        }

        return $this->setJsonDataResponse($data, 200);
    }

    public function fetchPropertyCheckResult(Request $request)
    {
        $username = config('very_basic_auth.user');
        $password = config('very_basic_auth.password');
        $url = config('app.url') . '/' . tenant('id') . '/api/v2/property-exist?';
        $params = preg_replace('/\s+/', '', $request->get('refs'));
        $params = http_build_query([
            'refs' => $params
        ], '', '&', PHP_QUERY_RFC3986);
        $params = str_replace('%2C', ',', $params);

        $client = new Client();

        try {
            $response = $client->request('POST', $url . $params, [
                'auth' => [$username, $password],
            ]);
            return response()->json(json_decode($response->getBody(), true));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function propertyChecker(Request $request)
    {
        return view('tenants.property.checker');
    }


    public function getPropertyXML()
    {
        try {

            $properties = $this->_propertyService->getPropertyXML();
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $properties;
    }

    public function getPropertiesByRefs(Request $request)
    {
        $refs = $request->get('refs');
        $aid  = $request->get('aid');

        if (empty($refs)) {
            return [];
        }

        if (is_string($refs))
            $refs =  explode(',', $refs);

        $getOnlyIds = (bool)((int)$request->get('getIDsOnly', 0) === 1);

        // $cache_key = Utilities::createCacheKey('property', implode("-", $refs), $aid);
        // $tags = [Utilities::slugify(config("app.name")), $cache_key];
        // $tags = array_merge($tags, $refs);
        // if (config("cache.default") == "memcached") {
        //     return Cache::tags($tags)->remember($cache_key, $this->expiration_time, function () use ($refs, $getOnlyIds, $aid) {
        //         return $this->getProperty($refs, $getOnlyIds, $aid);
        //     });
        // } else {
        // return Cache::remember($cache_key, $this->expiration_time, function() use($refs, $getOnlyIds){
        return $this->getProperty($refs, $getOnlyIds, $aid);
        // });
        // }
    }

    private function getProperty($refs, $getOnlyIds, $aid)
    {
        $properties = Property::isPublic()
            ->whereIn('property_ref_field', $refs)
            ->with([
                'files' => function ($query) {
                    $query->orderBy('sequence_no_field');
                },
                'region',
                'locality',
                'property_type',
                'property_status',
                'project',
                'features',
                'consultant'  => function ($query) {
                    $query->withCount('properties');
                }
            ])
            ->get();

        if ($getOnlyIds === false) {

            $property_inner_collections = [];

            foreach ($properties as $property) {

                $garage = $property->features->where('pivot.feature_value', 'Car Space')->first();
                if ($garage) {
                    $garage_value = $garage->pivot->feature_value;
                }
                $consultant_transformed = [];

                if (isset($property['consultant_id'])) {
                    $consultant = $property->consultant;
                }
                if (!empty($consultant)) {
                    $consultant_transformed = [
                        'id'             => $consultant->id ?? null,
                        'old_id'         => $consultant->old_id ?? null,
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
                        'url_field' => $consultant->url_field ?? $consultant->orig_consultant_image_src  ?? config('url.consultant_thumbnail'),
                        'image_status_field' => $consultant->image_status_field ?? null,
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



                $property_inner_collections[] = new PropertyInnerDTO(
                    $property['slug'],
                    $property['property_ref_field'],
                    $property['id'],
                    $property['market_status_field'],
                    $property['expiry_date_time'],
                    $property['market_type_field'],
                    $property['commercial_field'],
                    $property['region_field'],
                    $property->region,
                    $property['locality_id_field'],
                    $property->locality,
                    $property['property_type_id_field'],
                    $property->property_type,
                    $property['property_status_id_field'],
                    $property->property_status,
                    $property['price_field'],
                    $property['old_price_field'],
                    $property['premium_field'],
                    $property['rent_period_field'],
                    $property['date_available_field'],
                    $property['por_field'],
                    $property['description_field'],
                    $property['title_field'],
                    $property['long_description_field'],
                    $property['specifications_field'],
                    $property['items_included_in_price_field'],
                    (bool) $property['sole_agents_field'],
                    $property->propertyBlock,
                    $property['bedrooms_field'],
                    $property['bathrooms_field'],
                    $garage_value->feature_value ?? null,
                    $property['contact_details_field'],
                    $property['is_property_of_the_month_field'],
                    $property['is_featured_field'],
                    $property['is_hot_property_field'],
                    $property['date_on_market_field'],
                    $property['date_off_market_field'],
                    $property['date_price_reduced_field'],
                    $property['virtual_tour_url_field'],
                    $property['show_on_3rd_party_sites_field'],
                    $property['prices_starting_from_field'],
                    $property['hot_property_title_field'],
                    $property['area_field'],
                    $property['plot_area_field'],
                    $property['external_area_field'],
                    $property['internal_area_field'],
                    $property['weight_field'],
                    $this->_featureTransformer->transformCollection($property->features->sortBy('sort_order')->toArray()),
                    $files_transformed,
                    $consultant_transformed,
                    $property['latitude_field'],
                    $property['longitude_field'],
                    $property['show_in_searches'],
                    $property['three_d_walk_through'],
                    $property['is_managed_property'],
                    $property['project_id'],
                    $property->project,
                    $property['orig_created_at'],
                    $property['updated_at']
                );
            }


            if (isset($aid)) { //agent_code_id
                $consultant = Consultant::where('agent_code', $aid)->first();
                if (empty($consultant)) {
                    $consultant = Consultant::find($aid);
                }
                if (!empty($consultant)) {

                    $consultant_aid = [
                        'id'             => $consultant->id,
                        'old_id'         => $consultant->old_id,
                        'fullNameField'   => $consultant->full_name_field,
                        'imageFilenameField' => $consultant->image_file_name_field,
                        'imageNameField'       => $consultant->image_name_field,
                        'branchId'      => $consultant->branch_id_field,
                        'descriptionField'    => $consultant->description_field,
                        'designationField'    => $consultant->designation_field,
                        'contactNumberField' => $consultant->contact_number_field,
                        'officePhoneField'   => $consultant->office_phone_field,
                        'emailField'          => $consultant->email_field,
                        'isAvailable'    => $consultant->is_available,
                        'sourcePhotoUrl'  => $consultant->orig_consultant_image_src,
                        'url_field' => $consultant->url_field ?? $consultant->orig_consultant_image_src ?? config('url.consultant_thumbnail'),
                        'image_status_field' => $consultant->image_status_field ?? null,
                        'listingCounts' => $consultant->properties()->count()
                    ];
                }
            }

            $new_propertiesTransformed = [];
            foreach ($property_inner_collections as $property) {
                $formatted_features = [];
                foreach ($property->features as $feature) {
                    $formatted_features[$feature["id"]] = $feature;
                }
                $property->formatted_features = $formatted_features;
                if (!empty($consultant_aid)) {
                    $property->consultant = $consultant_aid;
                }
                $new_propertiesTransformed[] = $property;
            }

            return $new_propertiesTransformed;
        }

        return collect($properties->get())->map(function ($property) {
            return strtoupper($property->id ?? null);
        });
    }
}
