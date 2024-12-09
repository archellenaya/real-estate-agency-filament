<?php

namespace App\Components\Repositories;

interface ISavedSearchRepository
{
    public function getById($id);

    public function createSaveSearch(
        $name,
        $user_id,
        $url,
        $alerts,
        $email_frequency_id
    );
    
    public function getAll(int $per_page,int $user_id,?int $force_page=null);


    public function getByUrlUser(string $url , int $user_id);

    public function updateSaveSearch(
        $id,
        $name,
        $url,
        $alerts,
        $email_frequency_id
    );
}
