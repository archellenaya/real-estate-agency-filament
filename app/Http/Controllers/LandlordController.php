<?php

namespace App\Http\Controllers;


use App\Components\Services\ILandlordService;
use Illuminate\Http\Request;
use App\Constants\Components\DataProviders;

class LandlordController  extends Controller
{
    private $_landlordService;

    public function __construct(ILandlordService $landlordService)
    {
        $this->_landlordService = $landlordService;
    }

    public function index()
    {
        $tenants = $this->_landlordService->getAllTenants();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $tenant = [
            'name' => $request->tenant_name,
            'data_provider' => $request->data_provider ?? DataProviders::REAP,
            'status' => 0
        ];

        $tenant_model = $this->_landlordService->addTenants($tenant);

        return redirect("/admin/tenants/$tenant_model->id/edit")->with('message', 'New tenant has been created successfully.');
    }

    public function edit(Request $request, $tenant_id)
    {
        $tenant = $this->_landlordService->getTenant($tenant_id);
        if (isset($tenant) && $tenant)
            return view('tenants.edit', compact('tenant'));
        else
            return redirect()->back()->with('error', 'Tenant id not found.');
    }

    public function update(Request $request, $tenant_id)
    {
        $api_key = null;
        if ($request->data_provider == DataProviders::NOTION) {
            $api_key =  $request->api_key_notion;
        }
        if ($request->data_provider == DataProviders::AGILIS) {
            $api_key =  $request->api_key_agilis;
        }


        $tenant_update = [
            'name' => $request->tenant_name,
            'data_provider' => $request->data_provider ?? null,
            'username' => $request->username ?? null,
            'password' => $request->password ?? null,
            'cms_url' => $request->cms_url ?? null,
            'api_key' =>  $api_key,
            'system_identifier' => $request->identifier ?? null,
            'google_client_id' => $request->google_client_id ?? null,
            'google_client_secret' => $request->google_client_secret ?? null,
            'google_redirect' => $request->google_redirect ?? null,
            'facebook_client_id' => $request->facebook_client_id ?? null,
            'facebook_client_secret' => $request->facebook_client_secret ?? null,
            'facebook_redirect' => $request->facebook_redirect ?? null,
            'linkedin_client_id' => $request->linkedin_client_id ?? null,
            'linkedin_client_secret' => $request->linkedin_client_secret ?? null,
            'linkedin_redirect' => $request->linkedin_redirect ?? null,
            'status' => $request->status == 'publish' ? 1 : 0,
            'app_url' => $request->app_url ?? null,
            'frontend_url' => $request->frontend_url ?? null,
            'preheader_name' => $request->preheader_name ?? null,
            'mail_mailer' => $request->mail_mailer ?? null,
            'mail_host' => $request->mail_host ?? null,
            'mail_port' => $request->mail_port ?? null,
            'mail_username' => $request->mail_username ?? null,
            'mail_password' => $request->mail_password ?? null,
            'mail_encryption' => $request->mail_encryption ?? null,
            'mail_from_address' => $request->mail_from_address ?? null,
            'mail_from_name' => $request->mail_from_name ?? null,
            'logo_url' => $request->logo_url ?? null,
            'bed_icon_url' => $request->bed_icon_url ?? null,
            'bath_icon_url' => $request->bath_icon_url ?? null,
            'instagram_icon_url' => $request->instagram_icon_url ?? null,
            'facebook_icon_url' => $request->facebook_icon_url ?? null,
            'facebook_url' => $request->facebook_url ?? null,
            'instagram_url' => $request->instagram_url ?? null,
            'company_address' => $request->company_address ?? null,
            'color_code_1' => $request->color_code_1 ?? null,
            'color_code_2' => $request->color_code_2 ?? null,
            'color_code_3' => $request->color_code_3 ?? null,
            'color_code_4' => $request->color_code_4 ?? null,
            'color_code_5' => $request->color_code_5 ?? null,
            'mail_theme_header_style' => $request->mail_theme_header_style ?? null,
            'mail_theme_footer_style' => $request->mail_theme_footer_style ?? null,
            'copyright_text' => $request->copyright_text ?? null,

            'property_thumbnail' => $request->property_thumbnail ?? null,
            'consultant_thumbnail' => $request->consultant_thumbnail ?? null,
            'theme_font_style' => $request->theme_font_style ?? null,
            'consultant_reap_id' => $request->consultant_reap_id ?? null,
            'consultant_reap_name' => $request->consultant_reap_name ?? null,

            'form_property_enquiry_admin_recipients' => $request->form_property_enquiry_admin_recipients ?? null,
            'form_property_enquiry_email_cc' => $request->form_property_enquiry_email_cc ?? null,
            'form_property_enquiry_notify_assigned_consultant' => $request->form_property_enquiry_notify_assigned_consultant ?? null,
            'form_property_enquiry_notify_assigned_consultant_branch' => $request->form_property_enquiry_notify_assigned_consultant_branch ?? null,
        ];

        try {

            $this->_landlordService->updateTenant($tenant_update, $tenant_id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('message', 'You have successfully updated the tenant.');
    }

    public function delete($tenant_id)
    {
        try {
            $this->_landlordService->deleteTenant($tenant_id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }

        return redirect()->back()->with('message', 'You successfully deleted the tenant.');
    }
}
