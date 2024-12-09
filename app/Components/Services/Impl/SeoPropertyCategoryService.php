<?php

namespace App\Components\Services\Impl;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Passive\Utilities;
use App\Constants\Components\SEOPropertyFields;
use App\Components\Services\ISeoPropertyCategoryService;
use App\Components\Repositories\ISeoPropertyCategoryRepository;
use App\Constants\Components\Regions;

class SeoPropertyCategoryService implements ISeoPropertyCategoryService {
    
    private $_seoPropertyRepository;

    private $_default_options;

    public function __construct(ISeoPropertyCategoryRepository $seoPropertyRepository) 
    {
        $this->_seoPropertyRepository  = $seoPropertyRepository;
        $defaults = explode(',', config('app.default_options'));
        $this->_default_options = array_map(function($item) {
            return strtolower(trim($item));
        }, $defaults);
    }

    public function getAll()
    {
        return $this->_seoPropertyRepository->getAll();
    }

    public function getAllSlug()
    {
        return $this->_seoPropertyRepository->getAllSlug();
    }

    public function getBySlugOrFail($slug)
    {
        $result = $this->getBySlug($slug);
        if(empty($result)) {
            throw new ProcessException(
                ProcessExceptionMessage::SEO_PROPERTY_CATEGORY,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $result;
    }

    public function getBySlug($slug)
    {
        return $this->_seoPropertyRepository->getBySlug($slug);
    }

    public function getQueryVarsBySlug($slug)
    {
        return $this->_seoPropertyRepository->getQueryVarsBySlug($slug);
    }

    public function generatePropertySeo_Old($property)
    {
        $property_fields = $this->generateSEOByProperty($property);
 
        $fields = $this->_seoPropertyRepository->getConfigurations()->pluck('id')->toArray();
        
        $combinations = array();
        $this->createSEOCombinationField($fields, "", $combinations);
        foreach($combinations as $fields) {
            $fields = explode(',', $fields);
            $slug = '';
            $query_vars = '';
            foreach($property_fields as $property_field) {
                if(in_array($property_field['id'], $fields)) {
                    $ext = $slug == '' ? '' : '-';
                    $query = $slug == '' ? '' : '&';
                    if(empty($slug) && $property_field['field'] == SEOPropertyFields::LOCALITY) {
                        $slug = 'property-';
                    }
                    $slug .= $ext.$property_field['slug'];
                    $query_vars .= $query.$property_field['url_query'];

                    if(empty($this->getBySlug(mb_strtolower($slug)))) {
                        $this->_seoPropertyRepository->create(
                            mb_strtolower($slug),
                            mb_strtolower($query_vars)
                        );
                    }
                }
            }
        }
    }

    private function generateSEOByProperty($property, $skip_property_fields = [])
    {
        $fields = $this->_seoPropertyRepository->getConfigurations();
        $property_fields = []; 
        foreach($fields as $field) { 
            $property_field = $field->property_field;
            if(in_array($property_field, $skip_property_fields)) {
                continue;
            }

            switch ($property_field) {
                case SEOPropertyFields::MARKET_TYPE:
                    if(!empty($property->market_type_field)) {
                        $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field;
                        $market_type_vars = $property->market_type_field == 'Sale' ? 'forSale' : 'toLet';
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' => sprintf('for-%s', Utilities::slugify($market_type)),
                            'url_query' => sprintf('%s=1',  Utilities::slugify($market_type_vars))
                        ];
                    }
                    break; 
                    
                case SEOPropertyFields::BEDROOM:
                    if(!empty($property->bedrooms_field)) {
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' => sprintf('%s-%s', $property->bedrooms_field, Str::plural($property_field, $property->bedrooms_field)),
                            'url_query' => sprintf('%s=%s', Str::plural($property_field), $property->bedrooms_field)
                        ];
                    }
                    break;

                case SEOPropertyFields::BATHROOM:    
                    if(!empty($property->bathrooms_field)) {
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' =>  sprintf('%s-%s', $property->bathrooms_field, Str::plural($property_field, $property->bathrooms_field)),
                            'url_query' =>  sprintf('%s=%s', Str::plural($property_field), $property->bathrooms_field)
                        ];
                    }
                    break;

                case SEOPropertyFields::LOCALITY:
                    if(!empty($property->locality->locality_name)) {
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' => sprintf('in-%s', Utilities::slugify($property->locality->locality_name)),
                            'url_query' => sprintf('%s=%s',  Str::plural($property_field), Utilities::slugify($property->locality_id_field))
                        ];
                    }
                    break;
               
                case SEOPropertyFields::PROPERTY_TYPE:
                    if(!empty($property->property_type->description)) {
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' => Utilities::slugify(sprintf('%s', $property->property_type->description)),
                            'url_query' => sprintf('%s=%s',  'propertytype', Utilities::slugify($property->property_type_id_field))
                        ];
                        
                    }
                    break;

                case SEOPropertyFields::REGION:
                    $region = Utilities::getRegionByID($property->region_field);
                    if(!empty($property->region_field) && !empty($region->description)) {
                        $property_fields[] = [
                            'id' => $field->id,
                            'field' => $property_field,
                            'slug' => Utilities::slugify(sprintf('in-%s', $region->description)),
                            'url_query' => sprintf('%s=%s', "region", Utilities::slugify($property->region_field))
                        ]; 
                    }
                    break; 
            } 
        }

        return $property_fields;
    }

    private function createSEOCombinationField($arr, $temp_string = "", &$collect) {
        $ext = "";
        if ($temp_string != "") {
            $collect []= $temp_string;
            $ext = ",";
        }
    
        for ($i=0, $iMax = sizeof($arr); $i < $iMax; $i++) {
            $arrcopy = $arr;
            $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
            if (sizeof($arrcopy) > 0) {
                $this->createSEOCombinationField($arrcopy, $temp_string . $ext . $elem[0], $collect);
            } else {
                $collect [] = $temp_string . $ext . $elem[0];
            }   
        }
    }

    public function generateCombinations($arr, $length) {
        $combinations = [];
    
        $total = count($arr);
        $indexes = range(0, $total - 1);
    
        $generate = function($start = 0, $combination = []) use (&$generate, &$combinations, $total, $length, $arr, $indexes) {
            if (count($combination) === $length) {
                $combinations[] = $combination;
                return;
            }
    
            for ($i = $start; $i < $total; $i++) {
                if (!in_array($i, $combination)) {
                    $combination[] = $i;
                    $generate($i + 1, $combination);
                    array_pop($combination);
                }
            }
        };
    
        $generate();
    
        $result = [];
        foreach ($combinations as $combination) {
            $itemCombination = [];
            foreach ($combination as $index) {
                $itemCombination[] = $arr[$index];
            }
            $result[] = $itemCombination;
        }
    
        return $result;
    }
    
    public function generatePropertySeo($property, $skip_property_fields = [])
    {
        $property_fields = $this->generateSEOByProperty($property, $skip_property_fields); 
 
        for($i=1; $i<=count($property_fields); $i++) {
            $combinations = $this->generateCombinations($property_fields, $i);
            
            foreach($combinations as $combination) {
                $slugs_combi_arr    = [];
                $queryvar_combi_arr = [];
                foreach($combination as $combi_item) {
                    $slugs_combi_arr[] = $combi_item['slug'];
                    $queryvar_combi_arr[] = $combi_item['url_query'];
                } 

                $slug = implode("-", $slugs_combi_arr);
            
                $queryvar = implode("&", $queryvar_combi_arr);

                $this->_seoPropertyRepository->create($slug, $queryvar);
            } 
        }
    }   

    public function generate_property_inner_slug($property)
    {
        //[market-type]-[property-type]-in-[locality]-[region]-[ref] 
        //for-sale-apartment-in-swieqi-malta-30107
        $slug = [];

        if (!empty($property->market_type_field)) {
            $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field;
            $slug[] = sprintf('for-%s', Utilities::slugify($market_type));
        }

        if (!empty($property->property_type->description) && !in_array(strtolower($property->property_type->description), $this->_default_options)) {
            $slug[] = Utilities::slugify(sprintf('%s', $property->property_type->description));
        }

        if (!empty($property->locality->locality_name) && !in_array(strtolower($property->locality->locality_name), $this->_default_options)) {
            $slug[] = sprintf('in-%s', Utilities::slugify($property->locality->locality_name));
        }

        $region = Utilities::getRegionByID($property->region_field);

        if ((!empty($property->region_field) && !empty($region->description)) && !in_array(strtolower($region->description), $this->_default_options)) {
            $slug[] = Utilities::slugify(sprintf('%s', $region->description));
        }

        $slug[] = $property->property_ref_field;

        return implode("-", $slug);
    } 

    public function generate_property_inner_title($property)
    {
        //[market-type]-[property-type]-in-[locality]-[region]-[ref] 
        //for-sale-apartment-in-swieqi-malta-30107
        $title = [];
      
        if(!empty( $property->market_type_field)) {
            $market_type = $property->market_type_field == 'Rental'  ? 'rent' : $property->market_type_field; 
            $title[] = sprintf('For %s', ucfirst($market_type));
        }

        if(!empty($property->property_type->description)) {
            $title[] = ucfirst(sprintf('%s', $property->property_type->description)); 
        } 

        if(!empty($property->locality->locality_name)) {
            $title[] = sprintf('in %s', ucfirst($property->locality->locality_name));
        } 
 
        $region = Utilities::getRegionByID($property->region_field);
 
        if(!empty($property->region_field) && !empty($region->description)) {
            $title[] = ucfirst(sprintf('%s', $region->description));
        }

        $title[] = $property->property_ref_field;

        return implode(" ", $title);
    }

    public function getAllInnerPropertySlug()
    {
        return $this->_seoPropertyRepository->getAllInnerPropertySlug();
    }


    
}