<?php

namespace App\Components\Repositories\Impl;

use App\Models\SeoPropertyCategory;
use App\Models\Property;
use App\Models\SeoCategoryConfiguration;
use App\Components\Repositories\ISeoPropertyCategoryRepository;

class SeoPropertyCategoryRepository implements ISeoPropertyCategoryRepository
{
    public function getAll()
    { 
        return SeoPropertyCategory::select('id', 'slug', 'search_query_vars')->get();
    }

    public function getAllSlug()
    {  
        $tempSeoSlugs = SeoPropertyCategory::select('slug')->get();
        $seoSlugs = [];
        foreach( $tempSeoSlugs as $seo) {
            $seoSlugs[] = $seo->slug;
        }
        return $seoSlugs; 
    }
    
    public function create(
        $slug,
        $search_query_vars
    ) {
        return SeoPropertyCategory::updateOrCreate([
            'slug' => $slug, 
        ],[ 
            'search_query_vars' => $search_query_vars
        ]);
    }

    public function getBySlug($slug)
    {
        return SeoPropertyCategory::where("slug", $slug)->first();
    }

    public function getConfigurations()
    {
        return SeoCategoryConfiguration::select('property_field')->where('active', 1)->orderBy('sequence_no', 'asc')->get();
    }

    public function getQueryVarsBySlug($slug)
    {
        return SeoPropertyCategory::where("slug", $slug)->pluck('search_query_vars')->first();
    }

    public function getAllInnerPropertySlug()
    {
        $tempSeoSlugs = Property::select('slug')->whereNotNull('slug')->get();
        $seoSlugs = [];
        foreach( $tempSeoSlugs as $seo) {
            $seoSlugs[] = $seo->slug;
        }
        return $seoSlugs; 
    }
}
