<?php

namespace App\Components\Services;

interface ISeoPropertyCategoryService {
    
    public function getBySlugOrFail($slug);

    public function getBySlug($slug);

    public function generatePropertySeo($property, $skip_property_fields = []);

    public function getAll();

    public function getAllSlug();

    public function getQueryVarsBySlug($slug);

    public function generate_property_inner_slug($id); 

    public function generate_property_inner_title($property);

    public function getAllInnerPropertySlug();
}