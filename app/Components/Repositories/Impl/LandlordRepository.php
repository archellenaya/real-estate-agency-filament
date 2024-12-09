<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\ILandlordRepository;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;

class LandlordRepository implements ILandlordRepository
{
    public function getAllTenants()
    {
        return Tenant::paginate(10);
    }
 
    public function addTenants($data)
    {
        return Tenant::create($data);
    }

    public function updateTenantAccessToken($data, $id) 
    {
        $tenant = $this->getTenant($id);
        $tenant->access_token = $data['access_token'];
        $tenant->refresh_token = $data['refresh_token'];
        $tenant->save(); 

        config(['cms.access_token' => $tenant->access_token]);
        config(['cms.refresh_token' => $tenant->refresh_token]); 
    }

    public function updateTenant($data, $id)
    {
        $tenant = $this->getTenant($id); 

        if(isset($data['name']) && strcasecmp($data['name'], $tenant->name) != 0 ) {
            $tenant->id = Utilities::slugify($data['name']);
        }
        
        $tenant->name = $data['name'] ?? $tenant->name;
        $tenant->status = $data['status']?? $tenant->status;
        $tenant->data_provider = $data['data_provider'] ?? $tenant->data_provider;
        $tenant->api_key = $data['api_key'] ?? $tenant->api_key;
        $tenant->cms_url = $data['cms_url'] ?? $tenant->cms_url; 
        $tenant->username = $data['username'] ?? $tenant->username;
        $tenant->password = $data['password'] ?? $tenant->password;
        $tenant->system_identifier = $data['system_identifier'] ?? $tenant->system_identifier;

        $tenant->google_client_id = $data['google_client_id'] ?? $tenant->google_client_id;
        $tenant->google_client_secret = $data['google_client_secret'] ?? $tenant->google_client_secret;
        $tenant->google_redirect = $data['google_redirect'] ?? $tenant->google_redirect;

        $tenant->facebook_client_id = $data['facebook_client_id'] ?? $tenant->facebook_client_id;
        $tenant->facebook_client_secret = $data['facebook_client_secret'] ?? $tenant->facebook_client_secret;
        $tenant->facebook_redirect = $data['facebook_redirect'] ?? $tenant->facebook_redirect;

        $tenant->linkedin_client_id = $data['linkedin_client_id'] ?? $tenant->linkedin_client_id;
        $tenant->linkedin_client_secret = $data['linkedin_client_secret'] ?? $tenant->linkedin_client_secret;
        $tenant->linkedin_redirect = $data['linkedin_redirect'] ?? $tenant->linkedin_redirect;

        $tenant->app_url = $data['app_url'] ?? $tenant->app_url;
        $tenant->frontend_url = $data['frontend_url'] ?? $tenant->frontend_url;
        $tenant->facebook_url = $data['facebook_url'] ?? $tenant->facebook_url;
        $tenant->instagram_url = $data['instagram_url'] ?? $tenant->instagram_url;
        
        $tenant->mail_mailer = $data['mail_mailer'] ?? $tenant->mail_mailer;
        $tenant->mail_host = $data['mail_host'] ?? $tenant->mail_host;
        $tenant->mail_port = $data['mail_port'] ?? $tenant->mail_port;
        $tenant->mail_username = $data['mail_username'] ?? $tenant->mail_username;
        $tenant->mail_password = $data['mail_password'] ?? $tenant->mail_password;
        $tenant->mail_encryption = $data['mail_encryption'] ?? null;
        $tenant->mail_from_address = $data['mail_from_address'] ?? $tenant->mail_from_address;
        $tenant->mail_from_name = $data['mail_from_name'] ?? $tenant->mail_from_name;
        
        $tenant->bath_icon_url = $data['bath_icon_url'] ?? $tenant->bath_icon_url; 
        $tenant->bed_icon_url = $data['bed_icon_url'] ?? $tenant->bed_icon_url; 
        $tenant->facebook_icon_url = $data['facebook_icon_url'] ?? $tenant->facebook_icon_url; 
        $tenant->instagram_icon_url = $data['instagram_icon_url'] ?? $tenant->instagram_icon_url; 
        
        $tenant->color_code_1 = $data['color_code_1'] ?? $tenant->color_code_1; 
        $tenant->color_code_2 = $data['color_code_2'] ?? $tenant->color_code_2; 
        $tenant->color_code_3 = $data['color_code_3'] ?? $tenant->color_code_3; 
        $tenant->color_code_4 = $data['color_code_4'] ?? $tenant->color_code_4; 
        $tenant->color_code_5 = $data['color_code_5'] ?? $tenant->color_code_5; 
        $tenant->mail_theme_header_style = $data['mail_theme_header_style'] ?? $tenant->mail_theme_header_style; 
        $tenant->mail_theme_footer_style = $data['mail_theme_footer_style'] ?? $tenant->mail_theme_footer_style; 

        $tenant->preheader_name = $data['preheader_name'] ?? $tenant->preheader_name;
        $tenant->logo_url = $data['logo_url'] ?? $tenant->logo_url; 

        $tenant->copyright_text = $data['copyright_text'] ?? $tenant->copyright_text; 
        $tenant->company_address = $data['company_address'] ?? $tenant->company_address; 

        // $tenant->property_enquiry_cc = $data['property_enquiry_cc'] ?? $tenant->property_enquiry_cc;
        $tenant->property_thumbnail = $data['property_thumbnail'] ?? $tenant->property_thumbnail;
        $tenant->consultant_thumbnail = $data['consultant_thumbnail'] ?? $tenant->consultant_thumbnail;
        $tenant->theme_font_style = $data['theme_font_style'] ?? $tenant->theme_font_style;

        $tenant->consultant_reap_id = $data['consultant_reap_id'] ?? $tenant->consultant_reap_id;
        $tenant->consultant_reap_name = $data['consultant_reap_name'] ?? $tenant->consultant_reap_name;
        
        $tenant->form_property_enquiry_admin_recipients = $data['form_property_enquiry_admin_recipients'] ?? null;
        $tenant->form_property_enquiry_email_cc = $data['form_property_enquiry_email_cc'] ?? null;
        $tenant->form_property_enquiry_notify_assigned_consultant = $data['form_property_enquiry_notify_assigned_consultant'] ?? 'yes';
        $tenant->form_property_enquiry_notify_assigned_consultant_branch = $data['form_property_enquiry_notify_assigned_consultant_branch'] ?? 'yes';
        $tenant->save(); 
        
        return $tenant;
    }
    
    public function getTenant($id) 
    {
        return Tenant::findOrFail($id);
    }

    public function deleteTenant($id)
    {
        return Tenant::where('id', $id)->delete();
    }
}
