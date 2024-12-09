<?php

namespace App\Components\Repositories;

interface ISeoPropertyCategoryRepository
{
    public function getAll();

    public function getAllSlug();
    
    public function create(
        $slug,
        $search_query_vars
    );
    
    public function getBySlug($slug);

    public function getConfigurations();

    public function getQueryVarsBySlug($slug);

    public function getAllInnerPropertySlug();
}
