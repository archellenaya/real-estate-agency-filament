<?php

namespace App\Http\Controllers;
/**
 * API6
 */
use App\Components\Services\IEmailFrequencyService;
use App\Exceptions\ProcessException;

class EmailFrequencyController extends BaseController
{
    private $_emailFrequencyService;

    public function __construct(
        IEmailFrequencyService $emailFrequencyService
    )
    {
        $this->_emailFrequencyService = $emailFrequencyService;
    }

    public function index()
    {
        return $this->_emailFrequencyService->getAll();
    }

    public function show($id)
    {
        try {
            $emailFrequency = $this->_emailFrequencyService->getById($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($emailFrequency); 
    }
}