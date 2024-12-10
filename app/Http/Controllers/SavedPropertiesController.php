<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Components\Services\ISavedPropertyService;
use App\Components\Validators\ISavedPropertyValidator;

class SavedPropertiesController extends BaseController
{
    private $_savedPropertyService;
    private $_savedPropertyValidator;

    public function __construct(
        ISavedPropertyService $savedPropertyService,
        ISavedPropertyValidator $savedPropertyValidator
    ) {
        $this->_savedPropertyService = $savedPropertyService;
        $this->_savedPropertyValidator = $savedPropertyValidator;
    }

    public function sendEmail(Request $request)
    {
        $message                        = $request->message                     ?? null;
        $property_ids                   = $request->property_ids                ?? null;
        $properties_to_visit            = $request->properties_to_visit         ?? null;
        $tracking_agent_email           = $request->tracking_agent_email        ?? null;
        $tracking_agent_branch_email    = $request->tracking_agent_branch_email ?? null;
        $tracking_agent_name            = $request->tracking_agent_name         ?? null;
      
        $data = [
            'message'                       => $message,
            'property_ids'                  => $property_ids,
            'properties_to_visit'           => $properties_to_visit,
            'tracking_agent_email'          => $tracking_agent_email,
            'tracking_agent_name'           => $tracking_agent_name,
            'tracking_agent_branch_email'   => $tracking_agent_branch_email,
        ];

        $validator = $this->_savedPropertyValidator->validateSendEmailMoreInfo($data);
        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $user           = auth()->user();
            $userProfile    = $user ? $user->profile : null;
            $username       = $userProfile->first_name . ' ' . $userProfile->last_name;
        
            $this->_savedPropertyService->sendEmailForMorePropertiesInfo($message, $property_ids, $properties_to_visit, $username, $tracking_agent_branch_email, $tracking_agent_email, $tracking_agent_name);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }

    public function shareSavedProperties(Request $request)
    {
        $recipients = $request->recipients;
        $message = $request->message;

        $data = [
            'recipients' => $recipients,
            'message' => $message
        ];

        $validator = $this->_savedPropertyValidator->validateShareSavedProperty($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_savedPropertyService->shareSavedProperties($message, $recipients);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success share');
    }
}
