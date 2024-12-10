<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;

use App\Components\Services\ISavedSearchService;
use App\Components\Validators\ISavedSearchValidator;

class SavedSearchesController extends BaseController
{
    private $_savedSearchService;
    private $_savedSearchValidator;
    private $_userID;

    public function __construct(
        ISavedSearchService $savedSearchService,
        ISavedSearchValidator $savedSearchValidator
    ) {
        $this->_savedSearchService = $savedSearchService;
        $this->_savedSearchValidator = $savedSearchValidator;
        $this->middleware(function ($request, $next) {
            $this->_userID = auth('sanctum')->user()->id ?? null;
            return $next($request);
        });
    }

    public function index(Request $request, int $force_page = null)
    {
        $per_page = $request->per_page ?? 10;
        return $this->_savedSearchService->getAllSavedSearches($per_page, $this->_userID, $force_page);
    }

    /**
     * Checks if a SavedSearch Record exists with a given URL and User Id.
     *
     * @param Request $request
     * @return Response
     */
    public function exists(Request $request)
    {
        if (empty($request->url) === true) {
            //Missing Param
            return $this->setJsonDataResponse(['exists' => false], 400);
        }
        $saved_search = $this->_savedSearchService->getByUrlUser($request->url, $this->_userID);
        if (empty($saved_search) === true) {
            return $this->setJsonDataResponse(['exists' => false], 404);
        }

        return $this->setJsonDataResponse(['exists' => true]);
    }


    public function store(Request $request)
    {

        $name = $request->name;
        $type = $request->type;
        $url  = $request->url;
        $alerts = ($request->alerts ?? false) ? 1 : 0;
        $email_frequency_id = $request->email_frequency_id ?? null;

        $data = [
            'type' => $type,
            'name' => $name,
            'url' => $url,
            'user_id' => $this->_userID,
            'alerts' => $alerts,
            'email_frequency_id' => $email_frequency_id
        ];

        $validator = $this->_savedSearchValidator->validateStoreSavedSearch($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $savedSearch = $this->_savedSearchService->createNewSavedSearch(
                $type,
                $name,
                $this->_userID,
                $url,
                $alerts,
                $email_frequency_id
            );
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($savedSearch);
    }

    public function show($id)
    {
        $validator = $this->_savedSearchValidator->validateSavedSearchId([
            'id' => $id
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $savedSearch = $this->_savedSearchService->getSavedSearchDTO($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($savedSearch);
    }

    public function update(Request $request, $id)
    {
        $name   = $request->name ?? null;
        $url    = $request->url ?? null;
        $alerts = $request->alerts ?? 0;
        $email_frequency_id = $request->email_frequency_id ?? null;

        $data = [
            'id'                 => $id,
            'name'               => $name,
            'url'                => $url,
            'alerts'             => $alerts,
            'email_frequency_id' => $email_frequency_id
        ];

        $validator = $this->_savedSearchValidator->validateUpdateSavedSearch($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $savedSearch = $this->_savedSearchService->updateSavedSearch(
                $id,
                $name,
                $url,
                $alerts,
                $email_frequency_id
            );
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($savedSearch);
    }

    public function delete(Request $request, int $id)
    {
        $validator = $this->_savedSearchValidator->validateSavedSearchId([
            'id' => $id,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_savedSearchService->deleteSavedSearch($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        $page_to_use = $request->get('page', null);
        return $this->index($request, $page_to_use);
    }
}
