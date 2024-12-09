<?php 

namespace App\Components\Services;

interface ISavedSearchService
{
    public function getSavedSearch($id);

    public function createNewSavedSearch(
        $type_name,
        $name,
        $user_id,
        $url,
        $alerts = 0,
        $email_frequency_id
    );

    public function getByUrlUser(string $url,int $userId);

    public function getSavedSearchDTO($id);

    public function getAllSavedSearches(int $per_page,int $user_id);

    public function updateSavedSearch(
        $id,
        $name,
        $url,
        $alerts = 0,
        $email_frequency_id
    );

    public function deleteSavedSearch($id);
}