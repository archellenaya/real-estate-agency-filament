<?php

namespace App\Components\Services\Impl;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use App\Components\Services\IWishlistService;
use App\Components\Services\ISavedPropertyService;
use App\Components\Repositories\IPropertyRepository;
use App\Components\Services\IEmailNotificationService;
use App\Constants\Components\NotificationTriggerValue;

class SavedPropertyService implements ISavedPropertyService
{
    private $_emailNotificationService;

    private $_wishListService;

    private $_propertyRepository;

    public function __construct(
        IEmailNotificationService $emailNotificationService,
        IWishlistService $wishListService,
        IPropertyRepository $propertyRepository,
    ) {
        $this->_emailNotificationService = $emailNotificationService;
        $this->_wishListService = $wishListService;
        $this->_propertyRepository = $propertyRepository;
    }

    public function sendEmailForMorePropertiesInfo($message, $properties = [], $properties_to_visit = [], $username, ?string $tracking_agent_branch_email = null, ?string $tracking_agent_email = null, ?string $tracking_agent_name = null)
    {

        $interpolation_properties = $this->morePropertiesInfoInterpolation($message, $properties, $properties_to_visit, $username);

        $recipients = $this->getRecipientsForMoreInfo($interpolation_properties, $tracking_agent_branch_email, $tracking_agent_email, $tracking_agent_name);

        if (empty($recipients)) {
            $recipients = explode(',', config('company.form.property_enquiry.admin_recipients'));
        }

        //send an email to FS internal staff, as per business logic
        $this->_emailNotificationService->createEmailNotification(
            $interpolation_properties,
            NotificationTriggerValue::ON_MORE_INFO_PROPERTIES,
            $recipients
        );

        //send an email to the user, who submitted the form
        if ($fe_user_email = ($interpolation_properties['email'] ?? null)) {
            $this->_emailNotificationService->createEmailNotification(
                $interpolation_properties,
                NotificationTriggerValue::ON_MORE_INFO_PROPERTIES_FE_USER,
                [$fe_user_email]
            );
        }
    }

    /**
     * Function determines the internal staff recipients of the more info email , 
     * based on FS business logic
     *
     * @param string|null $tracking_agent_branch_email
     * @param string|null $tracking_agent_email
     * @return array|null
     */
    private function getRecipientsForMoreInfo(array &$interpolation_properties, ?string $tracking_agent_branch_email = null, ?string $tracking_agent_email = null, ?string $tracking_agent_name = null): ?array
    {

        $isAgentTracked = !empty($tracking_agent_branch_email) || !empty($tracking_agent_email);

        $enquiriesEmails = config('company.form.property_enquiry.email_cc', '');
        $enquiriesEmails = array_filter(array_map('trim', explode(',', $enquiriesEmails)));

        // Set consultant name if agent tracking is active and tracking agent name is provided
        $trackedAgentName = $tracking_agent_name ?? null;
        $trackedAgentEmail = $tracking_agent_email ?? null;
        $trackedAgentBranchEmail = $tracking_agent_branch_email ?? null;

        if ($isAgentTracked && (!empty($trackedAgentName) || !empty($trackedAgentBranchEmail) || !empty($trackedAgentEmail))) {
            $interpolation_properties['tracking_agent_name'] = $trackedAgentName;
            $interpolation_properties['tracking_agent_email'] = $trackedAgentEmail;
            $interpolation_properties['tracking_agent_branch_email'] = $trackedAgentBranchEmail; // Changed to $trackedAgentBranchEmail
        }

        // Initialize recipients array
        $recipients = [];

        // If no agent is tracked, return enquiry emails
        if (!$isAgentTracked) {
            return !empty($enquiriesEmails) ? $enquiriesEmails : null;
        }

        // Add tracking agent's branch email if available
        if (!empty($tracking_agent_branch_email)) {
            $recipients[] = $tracking_agent_branch_email;
        } else {
            Log::warn("Missing tracking_agent_branch_email. Tracking agent email: " . $tracking_agent_email);
        }

        // Add tracking agent's email if available
        if (!empty($tracking_agent_email)) {
            $recipients[] = $tracking_agent_email;
        } else {
            Log::warn("Missing tracking_agent_email. Tracking agent branch email: " . $tracking_agent_branch_email);
        }

        // Merge the enquiry emails with recipients
        $recipients = array_filter(array_merge($recipients, $enquiriesEmails), function ($email) {
            return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        return !empty($recipients) ? $recipients : null;
    }

    public function sendEmailForPropertyChangeAlert(string $message, array $properties, User $user): bool
    {
        if (!filter_var($user->username, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $recipients = [$user->username];

        $interpolation_properties = $this->propertiesAlertsInterpolation($message, $properties);

        $this->_emailNotificationService->createEmailNotification(
            $interpolation_properties,
            NotificationTriggerValue::ON_MORE_INFO_PROPERTIES,
            $recipients
        );

        return true;
    }

    private function propertiesAlertsInterpolation() {}

    private function morePropertiesInfoInterpolation($message, $properties_refs, $properties_to_visit, $username)
    {

        $properties = [];
        $property_refs = '';
        if (!empty($properties_refs)) {
            foreach ($properties_refs as $key => $property_ref) {
                // $property_links[] = 
                $property = $this->_propertyRepository->getPropertyByRef($property_ref);

                $property_refs .=  (($key > 0 ? ' , ' : '') . $property_ref);

                $properties[]   = [
                    'ref'             => $property_ref,
                    'url'             => sprintf('%s/property/%s', config('url.frontend_url'), $property->slug),
                    'property_image'  => $property->files()->where('file_type_field', 'MainImage')->value('url_field'),
                    'property_title'  => $property->title_field,
                    'price'           => $property->price_field <= 1 ? 'P.O.R' :  number_format($property->price_field, 2),
                    'consultant'      => $property->consultant->full_name_field,
                    'visit_property'  => in_array($property_ref, $properties_to_visit ?? []),
                    'bathrooms'       => $property->bathrooms_field ?? 0,
                    'bedrooms'        => $property->bedrooms_field  ?? 0,
                ];
            }
        }

        return [
            'message'               => $message,
            'email'                 => (auth()->user() ? auth()->user()->username : null),
            'properties'            => $properties,
            'property_refs'         => $property_refs,
            'user'                  => auth()->user(),
            'username'              => $username,
            'properties_to_visit'   => $properties_to_visit
        ];
    }

    public function shareSavedProperties($message, $emails)
    {
        $recipients = array_map('trim', explode(',', $emails));

        $saved_lists = $this->_wishListService->getList();

        $interpolation_properties = $this->savedListInterpolation($message, $saved_lists);

        $this->_emailNotificationService->createEmailNotification(
            $interpolation_properties,
            NotificationTriggerValue::ON_SHARE_SAVED_PROPERTIES,
            $recipients
        );
    }

    private function savedListInterpolation($message, $saved_lists)
    {
        foreach ($saved_lists as $property_ref) {
            $property = $this->_propertyRepository->getPropertyByRef($property_ref);
            $property_links[] = sprintf('%s/property/%s', config('url.frontend_url'), $property->slug);
        }
        return [
            'message' => $message,
            'property_links' => join(' ', $property_links)
        ];
    }
}
