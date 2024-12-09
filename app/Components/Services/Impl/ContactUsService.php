<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IFormService;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Components\Repositories\IFormRepository;
use App\Constants\Components\FormValues;


class ContactUsService implements IFormService {

    private $_formRepository;

    public function __construct(IFormRepository $formRepository)
    {
        $this->_formRepository = $formRepository;
    }

    public function create($interpolation_properties, $form_type)
    {
        try 
        { 
            $recipient = $interpolation_properties['recipient'];
            unset($interpolation_properties['recipient']);
            $recipients = array_map('trim', explode(',', $recipient));
            
            return $this->_formRepository->processForm($interpolation_properties, $form_type, $recipients);

        } catch(\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_CONTACT_US_SUBMITION,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }

    public function getLists($form_type)
    {
        switch($form_type) 
        {
            case FormValues::CONTACT_US:
                return $this->_formRepository->getLists($form_type);

            default:
                throw new ProcessException(
                    ProcessExceptionMessage::FORM_DOES_NOT_EXIST,
                    StatusCode::HTTP_BAD_REQUEST
                ); 
        }
    }

}
