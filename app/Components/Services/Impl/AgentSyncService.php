<?php

namespace App\Components\Services\Impl;

use App\Components\Passive\Utilities;
use App\Components\Services\Impl\SyncUtilityService;
use App\Components\Services\IAgentSyncService;
use App\Components\Repositories\IAgentRepository;
use App\Constants\Components\FileStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\File;

class AgentSyncService extends SyncUtilityService implements IAgentSyncService
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
                Utilities::message("old: " . $agent->orig_consultant_image_src);
                Utilities::message("new: " . $transformed_agent_data['orig_consultant_image_src']);
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
            DB::table('data_imports')->where('id', $webhook->id)->update(['exception' => $e->getMessage(), 'status' => 'failed']);
            return 0;
        }
        return 1;
    }

    public function transform($data)
    {
        if (isset($data->name) && isset($data->id)) {
            $json_data = json_encode($data);
            $name = $data->name ?? "";
            $surname = $data->surname ?? "";
            $agent_name = trim($name) . " " . trim($surname);
            $id = $this->generate_consultant_id($agent_name); //(isset($data->agentCode) && $data->agentCode != "") ? $data->agentCode:$data->externalIdentifier;

            if (isset($data->profileImage->url)) {
                $image_path = $this->stripUrlQueryString($data->profileImage->url);
                $orig_filename = basename($image_path);
                $file_info = pathinfo($orig_filename);
                $ext = $file_info['extension'];
                $newfilename = tenant('id') . "-" .  Utilities::slugify($agent_name) . "." . $ext;
            }

            return [
                'id' => $id,
                'old_id' => $data->id,
                'external_id' => $data->externalIdentifier,
                'agent_code' => $data->agentCode ?? null,
                'full_name_field' => $agent_name,
                'contact_number_field' => $this->transformPhoneNumber($data->contactNumber) ?? null,
                'whatsapp_number_field' => $data->whatsAppNumber ?? null,
                'description_field' => $data->description ?? null,
                'office_phone_field' => $data->office->contactNumber ?? null,
                'branch_id_field' => $data->office->externalIdentifier ?? null,
                'email_field' => $data->email ?? null,
                'orig_consultant_image_src' =>  $image_path ?? null,
                'url_field' => $image_path ?? config('url.consultant_thumbnail') ?: null,
                'image_name_field' => $orig_filename ?? null,
                'image_file_name_field' => $newfilename ?? null,
                'is_available' => 1,
                'data' => $json_data,
                'to_synch' => 0,
                'image_status_field' => FileStatus::TO_OPTIMIZE
            ];
        } else {
            return [];
        }
    }
}
