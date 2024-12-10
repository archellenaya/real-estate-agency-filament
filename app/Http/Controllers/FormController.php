<?php
/**
 * API6
 */
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Validators\Impl\PropertyValidator;
use App\Components\Validators\Impl\CareerValidator;
use App\Components\Validators\Impl\ContactUsValidator;
use App\Components\Validators\Impl\AgentValidator;
use App\Components\Services\Impl\PropertyService;
use App\Components\Services\Impl\APIv5Service;
use App\Components\Services\Impl\CareerService;
use App\Components\Services\Impl\ContactUsService;
use App\Components\Services\Impl\AgentFormService;
use App\Constants\Components\FormValues;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Mail\MailTemplate;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Components\Services\IAgentService;

class FormController extends BaseController
{
    private $_careerFormValidator;
    private $_propertyValidator;
    private $_contactUsValidator;
    private $_careerFormService;
    private $_propertyService;
    private $_contactUsService;
    private $_agentService;
    private $_agentPropertyService;
    private $_agentValidator;
    
    public function __construct(
        CareerService    $careerFormService, 
        PropertyService  $propertyService, 
        ContactUsService $contactUsService,
        AgentFormService     $agentService,
        IAgentService $agentPropertyService
    ) {
        $this->_careerFormValidator = new CareerValidator();
        $this->_propertyValidator   = new PropertyValidator();
        $this->_contactUsValidator  = new ContactUsValidator();
        $this->_agentValidator      = new AgentValidator();
        $this->_careerFormService   = $careerFormService;
        $this->_propertyService     = $propertyService;
        $this->_contactUsService    = $contactUsService;
        $this->_agentService        = $agentService; 
        $this->_agentPropertyService = $agentPropertyService; 
        
    }

    public function processForm(Request $request, $slug) 
    {
        switch( $slug )
        {
            case FormValues::CAREER:
                return $this->careerForm($request);

            case FormValues::PROPERTY_ENQUIRY:
                return $this->propertyEnquiryForm($request);

            case FormValues::REGISTER_PROPERTY:
                return $this->propertyRegistrationForm($request);

            case FormValues::CONTACT_US:
                return $this->contactUsForm($request);

            case FormValues::ADD_AGENT_REVIEW:
                return $this->agentReviewForm($request);
    
            default:
                throw new ProcessException(
                    ProcessExceptionMessage::FORM_DOES_NOT_EXIST,
                    StatusCode::HTTP_BAD_REQUEST
                );
        }
    }
    
    private function careerForm(Request $request) 
    {
        $validator = $this->_careerFormValidator->validate($request->all());
        
        if($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $interpolation_properties = [
            'recipient'           => $request->recipient ?? 'NA',
            'fullname'            => $request->fullname ?? 'NA',
            'prefix_phone_number' => $request->prefix_phone_number ?? 'NA',
            'phone_number'        => $request->phone_number ?? 'NA',
            'email'               => $request->email ?? 'NA',
            'position'            => $request->position ?? 'NA',
            'cv_upload'           => $request->cv_upload,
            'cover_letter_upload' => $request->cover_letter_upload,
        ];
        
        try {
            $this->_careerFormService->create($interpolation_properties, FormValues::CAREER);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('career submitted successfully!');
    }

    private function propertyEnquiryForm(Request $request)
    {
        $validator = $this->_propertyValidator->validate($request->all());

        if($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        } 

        $property = $this->_propertyService->getPropertyByRef($request->property_id);
        if(empty( $property )) {
            return $this->setJsonMessageResponse(ProcessExceptionMessage::PROPERTY_NOT_EXIST, StatusCode::HTTP_NOT_FOUND);
        }
        $property_thumbnail = $this->_propertyService->getPropertyThumnail( $property );
        $agent = $this->_agentPropertyService->getAgentByID($request->consultant_id);
        if(empty( $agent )) {
            return $this->setJsonMessageResponse(ProcessExceptionMessage::CONSULTANT_NOT_FOUND, StatusCode::HTTP_NOT_FOUND);
        }
        $agent_branch_email = (null !== $agent->getBranch()) ? $agent->getBranch()->email:null;
        $payment_frequency = null;
        if(isset($property->rent_period_field)) {
            $payment_frequency = " /". strtolower(substr($property->rent_period_field, 0, 1)); 
        }
        $interpolation_properties = [
            'property_id'                   => $request->property_id          ?? 'NA', 
            'property_slug'                 => $property->slug                ?? 'NA', 
            'fullname'                      => $request->fullname             ?? 'NA',
            'email'                         => $request->email                ?? 'NA', 
            'phone_number'                  => $request->phone_number         ?? 'NA',
            'comment'                       => $request->comment              ?? 'NA', 
            'property_image'                => $property_thumbnail,
            'property_title'                => $property->property_type->description ?? $property->locality->locality_name ?? null,
            'price'                         => isset($property->price_field) && $property->price_field > 0 ? number_format($property->price_field,0):'POR' ,//price on request if property price is equal or smaller then 1
            'consultant'                    => $agent->full_name_field ?? 'NA', 
            'payment_frequency'             => $payment_frequency, 
            'agent_email'                   => $agent->email_field ?? null,
            'agent_branch_email'            => $agent_branch_email ?? null, 
            'market_type_field'             => $property->market_type_field ?? 'NA', 
            'bedrooms'                      => $property->bedrooms_field ?? 'NA', 
            'bathrooms'                     => $property->bathrooms_field ?? 'NA', 
        ];
       
        try
        {
            $success = $this->_propertyService->create($interpolation_properties, FormValues::PROPERTY_ENQUIRY);
            if(!$success) {
                return $this->setJsonMessageResponse(ProcessExceptionMessage::FAILED_TO_SUBMIT_PROPERTY_ENQUIRY_APPLICATION, StatusCode::HTTP_INTERNAL_SERVER_ERROR);
            }
        }  catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('property-enquiry submitted successfully!');
    } 

    private function propertyRegistrationForm(Request $request) 
    {
        $validator = $this->_propertyValidator->validateNewSubmitted($request->all());
        
        if($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $interpolation_properties = [
            'recipient'             => $request->recipient ?? 'NA',
            'name'                  => $request->name ?? 'NA',
            'surname'               => $request->surname ?? 'NA',
            'email'                 => $request->email ?? 'NA',
            'prefix_phone_number'   => $request->prefix_phone_number ?? 'NA',
            'phone_number'          => $request->phone_number ?? 'NA',
            'location'              => $request->location ?? 'NA',
            'residential'           => $request->residential ?? 'NA',  
            'number_of_bedroom'     => $request->number_of_bedroom ?? 'NA', 
            'number_of_bathroom'    => $request->number_of_bathroom ?? 'NA',
            'purpose'               => $request->purpose ?? 'NA',
            'property_description'  => $request->property_description ?? 'NA',
        ];

        try {
            $this->_propertyService->create($interpolation_properties, FormValues::REGISTER_PROPERTY);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('property-registration submitted successfully!');
    }

    private function contactUsForm(Request $request) 
    {
        $validator = $this->_contactUsValidator->validate($request->all());
        
        if($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $interpolation_properties = [
            'recipient'             => $request->recipient              ?? 'NA',
            'fullname'              => $request->fullname               ?? 'NA',
            'email'                 => $request->email                  ?? 'NA',
            'prefix_phone_number'   => $request->prefix_phone_number    ?? 'NA',
            'phone_number'          => $request->phone_number           ?? 'NA',
            'country'               => $request->country                ?? 'NA',
            'message'               => $request->message                ?? 'NA',
        ];

        try {
            $this->_contactUsService->create($interpolation_properties, FormValues::CONTACT_US);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('Concern submitted successfully!');
    }

    private function agentReviewForm(Request $request) 
    {
        $validator = $this->_agentValidator->validate($request->all());
        
        if($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $interpolation_properties = [
            'recipient'             => $request->recipient      ?? 'NA',
            'fullname'              => $request->fullname       ?? 'NA',
            'email'                 => $request->email          ?? 'NA',
            'country'               => $request->country        ?? 'NA',
            'review_comment'        => $request->review_comment ?? 'NA',  
        ];

        try {
            $this->_agentService->create($interpolation_properties, FormValues::ADD_AGENT_REVIEW);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('Review submitted successfully!');
    }

    public function getForms(Request $request, $slug)
    {
        try {
            switch( $slug )
            {
                // case FormValues::CAREER:
                //     $lists =  $this->_careerFormService->getLists(FormValues::CAREER);
                //     break;
                case FormValues::PROPERTY_ENQUIRY:
                    $lists =  $this->_propertyService->getLists(FormValues::PROPERTY_ENQUIRY);
                    break;
                // case FormValues::REGISTER_PROPERTY:
                //     $lists =  $this->_propertyService->getLists(FormValues::REGISTER_PROPERTY);
                //     break;
                // case FormValues::CONTACT_US:
                //     $lists =  $this->_contactUsService->getLists(FormValues::CONTACT_US);
                //     break;
                // case FormValues::ADD_AGENT_REVIEW:
                //     $lists =  $this->_agentService->getLists(FormValues::ADD_AGENT_REVIEW);
                //     break;
                default:
                    throw new ProcessException(
                        ProcessExceptionMessage::FUNCTION_UNVAILABLE,
                        StatusCode::HTTP_BAD_REQUEST
                    );
            }
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($lists);
    } 
}
