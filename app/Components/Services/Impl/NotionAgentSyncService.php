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

class NotionAgentSyncService extends SyncUtilityService implements IAgentSyncService
{

    private $_agentRepository;

    public function __construct(IAgentRepository $agentRepository)
    {
        parent::__construct();
        $this->_agentRepository  = $agentRepository;
    }

    public function bulk($raw_datas, $webhook)
    {
        $raw_datas = Utilities::recursiveTrim($raw_datas);

        $update_counts = 0;

        // Process each raw data item
        foreach ($raw_datas as $raw_data) {
            try {
                $update_counts += $this->process($raw_data, $webhook);
            } catch (Exception $e) {
                Log::debug($e->getMessage());
                return 0;
            }
        }

        return $update_counts;
    }

    public function process($raw_data, $webhook = null)
    {
        $raw_data = Utilities::recursiveTrim($raw_data);

        $transformed_agent_data = $this->transform($raw_data);

        try {

            if (count($transformed_agent_data) <= 0)
                return 0;

            $agent = $this->_agentRepository->getAgentByOldID($transformed_agent_data['old_id']);

            if ($agent) {
                $this->updateAgent($agent, $transformed_agent_data);
                Log::debug("Updated Consultant: " . $agent->getID());
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

        $agent_name = $data['name'];
        $old_id = $data['id'];

        if (!isset($agent_name) && !isset($old_id)) {
            return [];
        }

        $json_data = json_encode($data);

        $image_details = $this->processImage($data['avatar_url'] ?? '', $agent_name);

        return [
            'id' => $this->generate_consultant_id($agent_name),
            'old_id' => $old_id,
            'external_id' => $old_id,
            'agent_code' => null,
            'full_name_field' => $agent_name,
            'contact_number_field' => null,
            'whatsapp_number_field' => null,
            'description_field' => null,
            'office_phone_field' => null,
            'branch_id_field' => Branch::first()->id ?? null,
            'email_field' => $data['person']['email']  ?? null,
            'orig_consultant_image_src' =>  $image_details['image_path'] ?? null,
            'url_field' => $image_details['image_path'] ?? config('url.consultant_thumbnail') ?: null,
            'image_name_field' => $image_details['orig_filename'] ??  null,
            'image_file_name_field' => $image_details['newfilename'] ?? null,
            'is_available' => 1,
            'data' => $json_data,
            'to_synch' => 0,
            'image_status_field' => FileStatus::TO_OPTIMIZE
        ];
    }

    private function updateAgent($agent, &$transformed_agent_data)
    {
        if ($agent->image_name_field != $transformed_agent_data['image_name_field']) {
            $this->deleteOldAgentImage($agent);
        } else {
            unset($transformed_agent_data['orig_consultant_image_src']);
            unset($transformed_agent_data['image_name_field']);
            unset($transformed_agent_data['image_file_name_field']);
        }

        unset($transformed_agent_data['id']);
        $agent->update($transformed_agent_data);
    }

    private function deleteOldAgentImage($agent)
    {
        $file_path = public_path(tenant('id') . "/image/consultant/" . $agent->file_name_field);
        if (File::exists($file_path)) {
            File::delete($file_path);
        }
        Utilities::message("Removed old agent pic");
    }

    private function processImage($avatar_url, $agent_name)
    {
        $image_path = null;
        $orig_filename = null;
        $newfilename = null;

        if (!empty($avatar_url)) {
            $image_path = $this->stripUrlQueryString($avatar_url);
            $orig_filename = basename($image_path);
            $file_info = pathinfo($orig_filename);

            if (!empty($file_info['extension'])) {
                $ext = $file_info['extension'];
                $newfilename = tenant('id') . "-" . Utilities::slugify($agent_name) . "." . $ext;
            } else {
                $image_path = null;
                $orig_filename = null;
                $newfilename = null;
            }
        }

        return [
            'image_path' => $image_path,
            'orig_filename' => $orig_filename,
            'newfilename' => $newfilename
        ];
    }
}
