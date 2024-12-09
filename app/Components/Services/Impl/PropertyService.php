<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IPropertyService;
use App\Components\Services\IFormService;
use App\Components\Repositories\IFormRepository;
use App\Components\Repositories\IPropertyRepository;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Constants\Components\FormValues;
use Illuminate\Support\Facades\Log;
use App\Components\Services\IEmailNotificationService;
use App\Constants\Components\NotificationTriggerValue;
use Exception;
use App\Models\Property;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Storage;

class PropertyService implements IPropertyService, IFormService
{

    private $_propertyRepository;
    private $_formRepository;
    private $_emailNotificationService;

    public function __construct(
        IPropertyRepository $propertyRepository,
        IFormRepository $formRepository,
        IEmailNotificationService $emailNotificationService,
    ) {
        $this->_propertyRepository  = $propertyRepository;
        $this->_formRepository      = $formRepository;
        $this->_emailNotificationService = $emailNotificationService;
    }

    public function getPropertyXML()
    {
        try {
            return response(file_get_contents(Storage::path('property.xml')), 200)
                ->header('Content-Type', 'application/xml');
        } catch (Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FILE_NOT_FOUND,
                StatusCode::HTTP_NOT_FOUND
            );
        }
    }

    public function updateCreatePropertyXML()
    {
        // $properties = $this->_propertyRepository->getAllPropertiesNolimit(); 

        // Property::orderBy('orig_created_at', 'DESC')->get()
        $properties_list = [];

        Property::orderBy('orig_created_at', 'DESC')->chunk(100, function ($properties) use (&$properties_list) {
            $fe_base_url = config('url.frontend_url');
            foreach ($properties as $property) {
                $images = [];
                $thumbnail = '';
                $slug = isset($property->slug) ? $property->slug : $property->property_ref_field;
                $url =  $fe_base_url . "/property/" . $slug;
                foreach ($property->files as $file) {
                    $file_url = $file->url_field ?? $file->orig_image_src ??  config('url.property_thumbnail');

                    if ($file->file_type_field == 'MainImage') {
                        $thumbnail = $file_url;
                    } else {
                        $images['images'][] = $file_url;
                    }
                }

                $properties_list['listing'][] = [
                    'Title' => $property->property_ref_field ?? null,
                    'Location' => $property->locality->locality_name ?? null,
                    'Description' => $property->long_description_field ?? null,
                    'Contract-type' => $property->market_type_field ?? null,
                    'Price' => isset($property->price_field) && $property->price_field > 0 ? number_format($property->price_field, 0) : 'POR',
                    'Bathrooms' => $property->bathrooms_field  ?? null,
                    'Bedrooms' => $property->bedrooms_field ?? null,
                    'Type' => $property->property_type->description ?? null,
                    'Image' => $thumbnail ?? null,
                    'URL' => $url ?? null,
                    'Slideshow' => $images ?? null,
                    'PostCode' => $property->locality->post_code ?? null
                ];
            }
        });


        $properties_xml = ArrayToXml::convert($properties_list, [
            'rootElementName' => 'listings'
        ], true, 'UTF-8', '1.0');

        Storage::put('property.xml', $properties_xml);
    }

    public function getPropertyThumnail($property)
    {
        $default_property_thumb_image = config('url.property_thumbnail');

        if (empty($property) === true) {
            return $default_property_thumb_image;
        }

        if (empty($property->files)) {
            return $default_property_thumb_image;
        }

        foreach ($property->files as $file) {

            if (empty($file->mime)) {
                continue;
            }

            if (strpos($file->mime, 'image') === false) {
                continue;
            }

            if (empty($file->file_type_field) || $file->file_type_field !== 'MainImage') {
                continue;
            }

            if (empty($file->file_name_field)) {
                continue;
            }

            return $file->url_field ?? $file->orig_image_src;
        }
        return $default_property_thumb_image;
    }

    public function getBySlug($slug)
    {
        return $this->_propertyRepository->getBySlug($slug);
    }

    public function search($parameter = [], $limit = 10, $sort_order = 'latest')
    {
        return $this->_propertyRepository->search($parameter, $limit, $sort_order);
    }

    public function reducedPrice($limit = 10, $days_limit = 7)
    {
        return $this->_propertyRepository->reducedPrice($limit);
    }

    public function increasedPrice($limit = 10, $days_limit = 7)
    {
        return $this->_propertyRepository->increasedPrice($limit, $days_limit);
    }

    public function createPropertyWithRefOnly($reference)
    {
        return $this->_propertyRepository->createPropertyWithRefOnly($reference);
    }

    public function getPropertyImageByRef($reference)
    {
        return $this->_propertyRepository->getPropertyImageByRef($reference);
    }

    public function getPropertyByRef($reference)
    {
        return $this->_propertyRepository->getPropertyByRef($reference);
    }

    public function detachUserFromProperty($user, $property)
    {
        $this->_propertyRepository->detachUser($user, $property);
    }

    public function create($interpolation_properties, $form_type)
    {
        switch ($form_type) {
            case FormValues::PROPERTY_ENQUIRY:
                return $this->propertyEnquiry($interpolation_properties);

            case FormValues::REGISTER_PROPERTY:
                return $this->propertyRegistration($interpolation_properties);

            default:
                throw new ProcessException(
                    ProcessExceptionMessage::FORM_DOES_NOT_EXIST,
                    StatusCode::HTTP_BAD_REQUEST
                );
        }
    }

    private function propertyRegistration($interpolation_properties)
    {
        try {
            $recipient = $interpolation_properties['recipient'];

            unset($interpolation_properties['recipient']);

            $recipients = array_map('trim', explode(',', $recipient));

            $interpolation_properties['fullname'] = $interpolation_properties['name'] . " " . $interpolation_properties['surname'];

            return $this->_formRepository->processForm($interpolation_properties, FormValues::REGISTER_PROPERTY, $recipients);
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_SUBMIT_PROPERTY_REGISTRATION,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }
    private function propertyEnquiry($interpolation_properties)
    {
        try {
            $recipients = $this->handlePropertyEnquiryRecipients($interpolation_properties);

            if (empty($recipients)) { //report to developer if enquiry has no reciepient
                $developers_address = !empty(config("mail.no_recipient_report_address")) ? array_map('trim', explode(',', config("mail.no_recipient_report_address"))) : null;
                if (!empty($developers_address)) {
                    $recipients = $this->cleanRecipientsEmails($developers_address);
                } else {
                    return false;
                }
            }

            //notify branch manager
            // $this->_formRepository->processForm($interpolation_properties,FormValues::PROPERTY_ENQUIRY, $recipients);
            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM,
                $recipients
            );

            //notify user who submitted the enquiry
            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_CREATE_PROPERTY_QUERY_FORM_FE_USER,
                [$interpolation_properties['email'] ?? null]
            );
            // $this->_formRepository->processForm($interpolation_properties,FormValues::PROPERTY_ENQUIRY_FE_USER, [$interpolation_properties['email'] ?? null]);

            return true;
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_SUBMIT_PROPERTY_ENQUIRY_APPLICATION,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }


    private function handlePropertyEnquiryRecipients(array &$interpolation_properties)
    {
        $recipients = [];
        $notify_agent_assignee = config("company.form.property_enquiry.notify_assigned_consultant") ?? null;
        $notify_agent_assignee_office = config("company.form.property_enquiry.notify_assigned_consultant_branch") ?? null;
        $property_enquiry_cc = !empty(config("company.form.property_enquiry.email_cc")) ? array_map('trim', explode(',', config("company.form.property_enquiry.email_cc"))) : null;
        $admin_recipients = !empty(config("company.form.property_enquiry.admin_recipients")) ? array_map('trim', explode(',', config("company.form.property_enquiry.admin_recipients"))) : null;

        if (strcasecmp($notify_agent_assignee, 'yes') == 0) {
            $propertyAgentEmail = $interpolation_properties['agent_email'] ?? null;
            //add agent email
            if (empty($propertyAgentEmail) === true) {
                Log::warning("(Case 1.1) Error while sending email PropertyEnquiry, missing agent email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $recipients[] = $propertyAgentEmail;
            }
        }

        if (strcasecmp($notify_agent_assignee_office, 'yes') == 0) {
            //add branch email
            $branchEmail = $interpolation_properties['agent_branch_email'] ?? null;
            if (empty($branchEmail) === true) {
                Log::warning("(Case 1.2) Error while sending email PropertyEnquiry, missing branch email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $branchEmail = array_map('trim', explode(',', $branchEmail));
                $recipients = array_merge($recipients, $branchEmail);
            }
        }

        if (!empty($admin_recipients)) {
            $recipients = array_merge($recipients, $admin_recipients);
        }

        if (!empty($property_enquiry_cc)) {
            $recipients = array_merge($recipients, $property_enquiry_cc);
        }

        return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'default known case');
    }

    private function cleanRecipientsEmails(?array $recipients, ?array $interpolation_properties = null, string $debugInfo = null)
    {
        if (empty($recipients) === false) {
            $recipients = array_unique($recipients);
        }

        return $recipients;
    }

    private function propertyEnquiryOld($interpolation_properties)
    {
        try {
            // $recipient = $interpolation_properties['recipient'];//contains the branch manager email

            // unset($interpolation_properties['recipient']);

            if (isset($interpolation_properties['want_to_see_property']) && $interpolation_properties['want_to_see_property']) {
                $interpolation_properties['want_to_see_property'] = "Yes";
            } else {
                $interpolation_properties['want_to_see_property'] = "No";
            }

            $recipients = $this->handlePropertyEnquiryRecipients($interpolation_properties);
            if (empty($recipients)) {
                $recipients = $this->cleanRecipientsEmails($recipients);
            }
            // dd($recipients);
            //array_map('trim', explode(',', $recipient));

            //notify branch manager
            $this->_formRepository->processForm($interpolation_properties, FormValues::PROPERTY_ENQUIRY, $recipients);

            //notify user who submitted the enquiry
            $this->_formRepository->processForm($interpolation_properties, FormValues::PROPERTY_ENQUIRY_FE_USER, [$interpolation_properties['email'] ?? null]);

            return true;
        } catch (\Exception $e) {
            throw new ProcessException(
                ProcessExceptionMessage::FAILED_TO_SUBMIT_PROPERTY_ENQUIRY_APPLICATION,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Function is used to deduce the recipients of  propertyEnquiry email based on the IP location of the user who submitted it.
     * NB We are making use of CF IP capabilities to figure out the IP address geographical location
     *
     * @param array $interpolation_properties
     * @return array 
     */
    private function handlePropertyEnquiryRecipientsOld(array &$interpolation_properties)
    {
        $recipient      = $interpolation_properties['recipient'] ?? []; //contains the branch manager email
        $defaultRecipients  = array_map('trim', explode(',', $recipient));
        //in case we have missing property information
        if (empty($interpolation_properties['property']) === true) {
            //should never be the case
            return $defaultRecipients;
        }

        $senderCountry  = (strtoupper($interpolation_properties['ipCountry']  ?? 'MT'));
        $market_type_field = $interpolation_properties['market_type_field'];
        //overseas emails defined in env
        $overseasEmails = env('OVERSEAS_EMAILS', '');
        $overseasEmails = array_map('trim', explode(',', $overseasEmails));

        //marketing emails defined in env
        $marketingEmails = env('MARKETING_EMAILS', '');
        $marketingEmails = array_map('trim', explode(',', $marketingEmails));

        //enquiries emails defined in env
        $enquiriesEmails = env('ENQUIRIES_EMAILS', '');
        $enquiriesEmails = array_map('trim', explode(',', $enquiriesEmails));

        //enquiries emails defined in env
        $genericEnquiriesEmails = env('GENERIC_ENQUIRY_OVERSEAS_EMAIL', '');
        $genericEnquiriesEmails = array_map('trim', explode(',', $genericEnquiriesEmails));

        if (empty($enquiriesEmails) || empty($genericEnquiriesEmails) || empty($overseasEmails) || empty($marketingEmails)) {
            Log::warning('Error MISSING ENV VALUES WHILE SENDING PropertyEnquiry, please ensure all the following values contain valid emails: OVERSEAS_EMAILS,MARKETING_EMAILS,ENQUIRIES_EMAILS,GENERIC_ENQUIRY_OVERSEAS_EMAIL');
        }

        $propertyAgentEmail         = $interpolation_properties['agent_email']                  ?? null;
        $branchEmail                = $interpolation_properties['agent_branch_email']           ?? null;
        $branchMangerEmail          = $interpolation_properties['agent_branch_manager_email']   ?? null;

        $trackedAgentEmail              = $interpolation_properties['tracking_agent_email']                 ?? null;
        $trackedAgentBranchEmail        = $interpolation_properties['tracking_agent_branch_email']          ?? null;
        $trackedAgentBranchManagerEmail = $interpolation_properties['tracking_agent_branch_manager_email']  ?? null;
        $trackedAgentName               = $interpolation_properties['tracking_agent_name']                  ?? null;

        $isAgentTracked             = empty($trackedAgentEmail) === false || empty($trackedAgentBranchEmail) === false;
        $isLocalIP                  = $senderCountry === 'MT';
        $recipients                 = [];

        if (!empty($interpolation_properties['has_preferred_consultant']) &&  !empty($interpolation_properties['preferred_consultant'])) {
            $recipients = array_merge($recipients, $overseasEmails);
            return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'Case 0. Client has preferred consultant');
        }

        //handle tracked agent interpolation options changes
        if ($isAgentTracked === true && empty($trackedAgentName) === false) {
            $interpolation_properties['consultant'] = $trackedAgentName;
        }


        //1.Local IP with no agent tracker - goes to consultant + branch + branch manager(bcc fsre.enquiries@gmail.com account)
        if (($isLocalIP === true  || in_array($market_type_field, ['LongLet', 'ShortLet'])) && $isAgentTracked === false) {
            //add consultant email
            if (empty($propertyAgentEmail) === true) {
                Log::warning("(Case 1.1) Error while sending email PropertyEnquiry, missing agent email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $recipients[] = $propertyAgentEmail;
            }

            //add branch email
            if (empty($branchEmail) === true) {
                Log::warning("(Case 1.2) Error while sending email PropertyEnquiry, missing branch email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $branchEmail = array_map('trim', explode(',', $branchEmail));
                $recipients = array_merge($recipients, $branchEmail);
            }

            if (empty($branchMangerEmail) === true) {
                Log::warning("(Case 1.3) Warning while sending email PropertyEnquiry, missing branch manager email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $branchMangerEmail = array_map('trim', explode(',', $branchMangerEmail));
                $recipients = array_merge($recipients, $branchMangerEmail);
            }

            if (!in_array($market_type_field, ['LongLet', 'ShortLet'])) //if forSale then cc overseas
            {
                $recipients = array_merge($recipients, $overseasEmails);
            }

            if (in_array($market_type_field, ['LongLet', 'ShortLet'])) {
                // add enquiries emails
                $recipients = array_merge($recipients, $enquiriesEmails);
            }

            $otherCC = $this->getConsultantEmailCC($propertyAgentEmail);

            if (count($otherCC) > 0) {
                $recipients = array_merge($recipients, $otherCC);
            }

            return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'Case 1. Local IP with no agent tracker');
        }


        //2. Wishlist from local IP with agent tracker - goes to consultant in the agent tracker + branch of consultant in agent tracker + branch manager email(bcc fsre.enquiries@gmail.com account)
        if (($isLocalIP === true  || in_array($market_type_field, ['LongLet', 'ShortLet']))  && $isAgentTracked === true) {

            //add consultant email
            if (empty($trackedAgentEmail) === true) {
                Log::warning("(Case 2.1) Error while sending email PropertyEnquiry, missing agent email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $recipients[] = $trackedAgentEmail;
            }

            //add branch email
            if (empty($trackedAgentBranchEmail) === true) {
                Log::warning("(Case 2.2) Error while sending email PropertyEnquiry, missing branch email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $trackedAgentBranchEmail = array_map('trim', explode(',', $trackedAgentBranchEmail));
                $recipients = array_merge($recipients, $trackedAgentBranchEmail);
            }

            //add branch manager email
            if (empty($trackedAgentBranchManagerEmail) === true) {
                Log::warning("(Case 2.2) Error while sending email PropertyEnquiry, missing branch manager email, form-payload: " . json_encode($interpolation_properties));
            } else {
                $trackedAgentBranchManagerEmail = array_map('trim', explode(',', $trackedAgentBranchManagerEmail));
                $recipients = array_merge($recipients, $trackedAgentBranchManagerEmail);
            }

            if (!in_array($market_type_field, ['LongLet', 'ShortLet'])) //if forSale then cc overseas
            {
                $recipients = array_merge($recipients, $overseasEmails);
            }

            if (in_array($market_type_field, ['LongLet', 'ShortLet'])) {
                // add enquiries emails
                $recipients = array_merge($recipients, $enquiriesEmails);
            }

            $otherCC = $this->getConsultantEmailCC($trackedAgentEmail);

            if (count($otherCC) > 0) {
                $recipients = array_merge($recipients, $otherCC);
            }

            return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'Case 2. Wishlist from local IP with agent tracker');
        }


        //3. Wishlist from Foreign IP - goes to overseas@franksalt.com.mt and fs@franksalt.com.mt (Bcc fsre.enquiries@gmail.com account)
        if ($isLocalIP === false) {
            //add enquiries emails
            $recipients = array_merge($recipients, $overseasEmails, $genericEnquiriesEmails);
            return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'Case 3. Wishlist from Foreign IP');
        }

        //4. Unknown Case should never be reached, in this case we send to fsre.enquiries, to ensure data is not lost in the worst case scenario
        $recipients = array_merge($recipients, $enquiriesEmails);
        Log::error('(Case 4) Unknown case reached when sending PropertyEnquiry, form-payload: ' . json_encode($interpolation_properties));

        //in case an error , always send email, should be reached 
        return $this->cleanRecipientsEmails($recipients, $interpolation_properties, 'default unknown case');
    }

    /**
     * Removes repeated Emails from the given recipients array, ensures that the given array is reverted to default if empty
     *
     * @param array|null $recipients
     * @param array|null $interpolation_properties
     * @param string|null $debugInfo
     * @return array
     */
    private function cleanRecipientsEmailsOld(?array $recipients, ?array $interpolation_properties = null, string $debugInfo = null)
    {
        if (empty($recipients) === false) {
            $recipients = array_unique($recipients);
        }

        if (empty($recipients) === true) {
            $interpolation_properties['debug_info'] = $debugInfo;
            Log::error('(Case 5) Unknown case reached when sending PropertyEnquiry, form-payload: ' . json_encode($interpolation_properties));

            //overseas emails defined in env
            $overseasEmails = env('OVERSEAS_EMAILS', '');
            $overseasEmails = array_map('trim', explode(',', $overseasEmails));

            //enquiries emails defined in env
            $genericEnquiriesEmails = env('GENERIC_ENQUIRY_OVERSEAS_EMAIL', '');
            $genericEnquiriesEmails = array_map('trim', explode(',', $genericEnquiriesEmails));

            $recipients = array_merge($recipients, $overseasEmails, $genericEnquiriesEmails);
        }

        return $recipients;
    }


    public function getLists($form_type)
    {
        switch ($form_type) {
            case FormValues::PROPERTY_ENQUIRY:
                return $this->_formRepository->getLists($form_type);

            case FormValues::REGISTER_PROPERTY:
                return $this->_formRepository->getLists($form_type);

            default:
                throw new ProcessException(
                    ProcessExceptionMessage::FORM_DOES_NOT_EXIST,
                    StatusCode::HTTP_BAD_REQUEST
                );
        }
    }

    // private function getConsultantEmailCC($property_consultant_email)
    // {
    //     $to_cc_emails = [];

    //     foreach($this->team_leads_cc as $lead_email => $member_emails)
    //     {
    //         foreach($member_emails as $member_email)
    //         {
    //             if($property_consultant_email == $member_email)
    //             {
    //                 $to_cc_emails[] = $lead_email;
    //                 break;
    //             }
    //         }
    //     }

    //     return $to_cc_emails;
    // }



}
