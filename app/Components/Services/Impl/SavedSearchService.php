<?php

namespace App\Components\Services\Impl;

use App\Components\Repositories\ISavedSearchRepository;
use App\Components\Services\IEmailFrequencyService;
use App\Components\Services\ISavedSearchService;
use App\Components\Services\ITypeService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Models\DTO\SavedSearchDTO;

class SavedSearchService implements ISavedSearchService
{
    private $_savedSearchRepository;
    private $_typeService;
    private $_emailFrequencyService;

    public function __construct(
        ISavedSearchRepository $savedSearchRepository,
        ITypeService $typeService,
        IEmailFrequencyService $emailFrequencyService
    ) {
        $this->_savedSearchRepository = $savedSearchRepository;
        $this->_typeService = $typeService;
        $this->_emailFrequencyService = $emailFrequencyService;
    }

    public function getSavedSearch($id)
    {
        $savedSearch = $this->_savedSearchRepository->getById($id);

        if (empty($savedSearch)) {
            throw new ProcessException(
                ProcessExceptionMessage::SAVED_SEARCH_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $savedSearch;
    }

    public function createNewSavedSearch(
        $type_name,
        $name,
        $user_id,
        $url,
        $alerts = 0,
        $email_frequency_id
    ) {
        $email_frequency = '';

        if (!empty($email_frequency_id)) {
            $email_frequency = $this->_emailFrequencyService->getById($email_frequency_id);
        }
        //decode url , we will save it as a json object in db so we can query it by using mysql JSON parsing capabilities
        $decoded_url = null;
        parse_str($url, $decoded_url);

        unset($decoded_url['sort']);
        unset($decoded_url['pg']);

        $savedSearch = $this->_savedSearchRepository->createSaveSearch(
            $name,
            $user_id,
            $decoded_url ? json_encode($decoded_url) : $url,
            $alerts,
            $email_frequency->id ?? null
        );

        return $this->getSavedSearchDTO($savedSearch->id);
    }

    public function getSavedSearchDTO($id)
    {
        $savedSearch = $this->getSavedSearch($id);

        return new SavedSearchDTO(
            $savedSearch->id,
            $savedSearch->name,
            $savedSearch->url,
            $savedSearch->alerts,
            $savedSearch->email_frequency_id,
            $savedSearch->email_frequency->name ?? ''
        );
    }

    public function getAllSavedSearches(int $per_page, int $user_id, ?int $force_page = null)
    {
        return $this->_savedSearchRepository->getAll($per_page, $user_id, $force_page);
    }

    public function getByUrlUser(string $url, int $userId)
    {
        return $this->_savedSearchRepository->getByUrlUser($url, $userId);
    }



    public function updateSavedSearch(
        $id,
        $name,
        $url,
        $alerts = 0,
        $email_frequency_id
    ) {
        $savedSearch = $this->getSavedSearch($id);

        $type = '';
        $email_frequency = '';

        if (!empty($type_name)) {
            $type = $this->_typeService->getByName($type_name);
        }

        if (!empty($email_frequency_id)) {
            $email_frequency = $this->_emailFrequencyService->getById($email_frequency_id);
        }

        //decode url , we will save it as a json object in db so we can query it by using mysql JSON parsing capabilities
        $decoded_url = null;
        parse_str($url, $decoded_url);

        unset($decoded_url['sort']);
        unset($decoded_url['pg']);

        $this->_savedSearchRepository->updateSaveSearch(
            $id,
            $name,
            $decoded_url ? json_encode($decoded_url) : $url,
            $alerts,
            $email_frequency->id ?? null
        );

        return $this->getSavedSearchDTO($savedSearch->id);
    }

    public function deleteSavedSearch($id)
    {
        $savedSearch = $this->getSavedSearch($id);

        $savedSearch->delete();
    }
}
