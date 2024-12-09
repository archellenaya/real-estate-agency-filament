<?php

namespace App\Constants\Exception;

class ProcessExceptionMessage
{
    public const INVALID_USER_CREDENTIALS = "Invalid Credentials";
    public const USER_TYPE_DOES_NOT_EXIST = "User Type does not exist";
    public const USER_DOES_NOT_EXIST = "User or Email does not exist";
    public const USER_ALREADY_EXISTS = "User or Email already exists";
    public const PASSWORD_MISMATCH = "Password mismatch";
    public const CURRENT_PASSWORD_INVALID = "Current password is invalid";
    public const NOTIFICATION_TRIGGER_DOES_NOT_EXIST = "Notification trigger does not exist";
    public const EMAIL_TEMPLATE_DOES_NOT_EXIST = "Email template does not exist";
    public const EMAIL_NOTIFICATION_DOES_NOT_EXIST = "Email notification does not exist";
    public const ERROR_IN_SENDING_EMAIL = "Error in sending email";
    public const USER_ALREADY_ACTIVE = 'User is already activated';
    public const CODE_IS_INVALID = 'The code is invalid';
    public const USER_IS_NOT_ACTIVE = 'The user is not active';
    public const FAILED_TO_LOGIN_VIA_GOOGLE = 'Failed to login via Google';
    public const FAILED_TO_LOGIN_VIA_FACEBOOK = 'Failed to login via Facebook';
    public const USER_DOES_NOT_HAVE_PROFILE_INFO = 'Missing profile info';
    public const UNVERIFIED_GOOGLE_ACCOUNT = 'Unverified google account';
    public const TOKEN_HAS_EXPIRED = 'Token already expired';
    public const TOKEN_EXPIRY_MUST_BE_A_NUMBER = "Token expiry must be a number";
    public const FAILED_TO_UPDATE_ACCOUNT = 'Failed to update account';
    public const ACCOUNT_DEACTIVATED = 'Account deactivated';
    public const FAILED_TO_SUBMIT_CAREER_APPLICATION = 'Failed to submit career application';
    public const FAILED_TO_SUBMIT_PROPERTY_ENQUIRY_APPLICATION = 'Failed to submit property-enquiry application';
    public const FAILED_TO_SUBMIT_PROPERTY_REGISTRATION = 'Failed to submit property-registration';
    public const FAILED_CONTACT_US_SUBMITION = 'Failed contact-us submission';
    public const FAILED_TO_SUBMIT_AGENT_REVIEW = 'Failed to submit agent review';
    public const FORM_DOES_NOT_EXIST = "Form does not exist";
    public const SAVED_SEARCH_DOES_NOT_EXIST = 'The saved search does not exist.';
    public const TYPE_DOES_NOT_EXIST = 'The type does not exist.';
    public const EMAIL_FREQUENCY_DOES_NOT_EXIST = 'The email frequency does not exist.';
    public const PROPERTY_ALERT_DOES_NOT_EXIST = 'The property alert does not exist.';
    public const IMAGE_FILE_NOT_SUPPORTED = 'Image file not supported';
    public const IMAGE_FILE_UNABLE_TO_RETRIVE = 'Image file unable to retrieve';
    public const IMAGE_NOT_FOUND = 'Image not found';
    public const VIDEO_NOT_FOUND = 'Video not found';
    public const FUNCTION_UNVAILABLE = "Function not available";
    public const PROVIDER_NOT_SUPPORTED = "Provider not supported";
    public const UNDER_DEVELOPMENT = "Under development";
    public const FAILED_TO_LOGIN = "Failed to login";
    public const NO_EMAIL_FOUND = "No email was found";
    public const FAILED_TO_CREATE_PROPERTY = "Failed to create property";
    public const PROJECT_NOT_FOUND = "Project not found";
    public const UNKNOWN_PRICE_EVENT = "Price event unknown";
    public const SEO_PROPERTY_CATEGORY = "SEO property category not found.";
    public const PROPERTY_NOT_EXIST = "Property not found.";
    public const BRANCH_NOT_EXIST = "Branch not found.";
    public const REAP_PROPERTIES_NOT_WORKING = "REAP NOT AVAILABLE: Unable to connect PROPERTIES from REAP.";
    public const REAP_AGENTS_NOT_WORKING = "REAP NOT AVAILABLE: Unable to connect AGENTS from REAP.";
    public const REAP_BRANCH_NOT_WORKING = "REAP NOT AVAILABLE: Unable to connect BRANCHES from REAP.";
    public const MIDDLELAYER_SYNC_ERROR = "Middlelayer is getting error in Synching Data.";
    public const CONSULTANT_NOT_FOUND = "Consultant not found.";
    public const SOMETHING_NOT_RIGHT = "Something is not wrong, Please, Contact Admin.";
    public const FILE_NOT_FOUND = "File not found.";
    public const AGILIS_UNABLE_TO_RETRIEVE_TOKEN = "Agilis unable to retrieve token";
    public const AGILIS_PROPERTIES_NOT_WORKING = "AGILIS NOT AVAILABLE: Unable to connect PROPERTIES from REAP.";
    public const AGILIS_EXTRAS_NOT_WORKING = "AGILIS NOT AVAILABLE: Unable to connect EXTRAS from REAP.";
    public const NOTION_AGENTS_NOT_WORKING = "NOTION NOT AVAILABLE: Unable to connect AGENTS from NOTION.";
    public const NOTION_LOCALITY_AND_REGION_NOT_WORKING = "NOTION NOT AVAILABLE: Unable to connect LOCALITY AND REGION from NOTION.";
    public const NOTION_PROPERTY_TYPE_NOT_WORKING = "NOTION NOT AVAILABLE: Unable to connect PROPERTY TYPE from NOTION.";
    public const NOTION_PROPERTY_NOT_WORKING = "NOTION NOT AVAILABLE: Unable to connect PROPERTY from NOTION.";
}
