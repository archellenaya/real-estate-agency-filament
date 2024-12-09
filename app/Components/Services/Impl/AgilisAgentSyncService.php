<?php

namespace App\Components\Services\Impl;

use Exception;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Components\Passive\Utilities;
use App\Constants\Components\FileStatus;
use App\Components\Services\IAgentSyncService;
use App\Components\Repositories\IAgentRepository;
use App\Components\Services\Impl\SyncUtilityService;
use Illuminate\Support\Facades\File;

class AgilisAgentSyncService extends SyncUtilityService implements IAgentSyncService
{

    private $_agentRepository;

    public function __construct(IAgentRepository $agentRepository)
    {
        parent::__construct();
        $this->_agentRepository  = $agentRepository;
    }

    public function bulk($raw_datas, $webhook)
    {
        $update_counts = 0;
        foreach ($raw_datas as $raw_data) {
            try {
                $update_counts += $this->process($raw_data, $webhook);
            } catch (Exception $e) {
                Log::debug($e->getMessage());
                DB::table('data_imports')->where('id', $webhook->id)->update(['exception' => $e->getMessage(), 'status' => 'failed']);
                return 0;
            }
        }
        return $update_counts;
    }

    public function process($raw_data, $webhook = null)
    {
        try {
            $transformed_agent_data = $this->transform($raw_data);
            if (count($transformed_agent_data) <= 0)
                return 0;

            $agent = $this->_agentRepository->getAgentByOldID($transformed_agent_data['old_id']);

            if (isset($agent) && $agent) {
                $agent_id = $agent->getID();
                if ($agent->orig_consultant_image_src != $transformed_agent_data['orig_consultant_image_src']) {
                    $file_path = public_path(tenant('id') . "/image/consultant/" . $agent->file_name_field);
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }
                    Utilities::message("remove old agent pic");
                } else {
                    unset($transformed_agent_data['orig_consultant_image_src']);
                    unset($transformed_agent_data['image_name_field']);
                    unset($transformed_agent_data['image_file_name_field']);
                }
                unset($transformed_agent_data['id']);
                $agent->update($transformed_agent_data);
                Log::debug("Updated Consultant: " . $agent_id);
            } else {
                $this->_agentRepository->createAgent($transformed_agent_data);
                Log::debug("Created Consultant: " . $transformed_agent_data['id']);
            }
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            if (!empty($webhook))
                DB::table('data_imports')->where('id', $webhook->id)->update(['exception' => $e->getMessage(), 'status' => 'failed']);
            return 0;
        }
        return 1;
    }

    public function transform($data)
    {
        if (isset($data['reportingName']) && isset($data['id'])) {
            $json_data = json_encode($data);
            // $data = array_map('trim', $data);
            $agent_name = trim($data['reportingName']);
            $id = $this->generate_consultant_id($agent_name); //(isset($data->agentCode) && $data->agentCode != "") ? $data->agentCode:$data->externalIdentifier;

            if (!empty($data['fileUrl'])) {
                $image_path = $this->stripUrlQueryString($data['fileUrl']);
                $orig_filename = basename($image_path);
                $file_info = pathinfo($orig_filename);
                $ext = $file_info['extension'];
                $newfilename = tenant('id') . "-" .  Utilities::slugify($agent_name) . "." . $ext;
            }

            $default_branch = Branch::first();
            $default_branch_id = (isset($default_branch) && $default_branch) ? $default_branch->id : null;
            $phones = $data['phones'] ?? [];
            $contact_number = null;
            foreach ($phones as $phone) {
                if (strtolower($phone['phoneType']) == "mobile") {
                    $contact_number =  trim($phone['telephoneNo']);
                    break;
                }
            }

            return [
                'id' => $id,
                'old_id' => $data['id'],
                'external_id' => $data['id'],
                'agent_code' => $data['staffMemberCode'] ?? null,
                'full_name_field' => $agent_name ?? null,
                'contact_number_field' => $contact_number ?? null,
                'whatsapp_number_field' => $data['whatsapp_number_field']  ?? null,
                'description_field' => $this->removeHtmlAndEntities($data['webSignature']) ?? null,
                'office_phone_field' => $data['office_phone_field'] ?? null,
                'branch_id_field' => $data['branch_id_field'] ?? $default_branch_id,
                'email_field' => $data['mainEmail']  ?? null,
                'orig_consultant_image_src' =>  $data['fileUrl'] ?? null,
                'url_field' => $data['fileUrl'] ?? config('url.consultant_thumbnail') ?: null,
                'image_name_field' => $orig_filename ?? null,
                'image_file_name_field' => $newfilename ?? null,
                'is_available' => isset($data['publishToWebsite']) ? $data['publishToWebsite'] : null,
                'data' => $json_data,
                'to_synch' => 0,
                'image_status_field' => FileStatus::TO_OPTIMIZE
            ];
        } else {
            return [];
        }
    }
}
