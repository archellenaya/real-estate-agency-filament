<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IProjectSyncService;
use App\Components\Services\Impl\SyncUtilityService;
use Illuminate\Support\Facades\Log;
use App\Components\Repositories\IProjectRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\DataImport;
use Exception;

class ProjectSyncService extends SyncUtilityService implements IProjectSyncService {
    
    private $_projectRepository;

    public function __construct(IProjectRepository $projectRepository) 
    {
        parent::__construct();
        $this->_projectRepository  = $projectRepository;
    }

    public function bulk($raw_datas, $webhook)
    {   
        $update_counts = 0;
        foreach($raw_datas as $raw_data) {
            $update_counts += $this->process($raw_data, $webhook);
        }
        return $update_counts;
    }

    public function process($raw_data, $webhook)
    {
        $webhook = DataImport::where('id', $webhook->id)->first();
        try {
            $transformed_data = $this->transform($raw_data);
            $project = $this->_projectRepository->getProjectByOldID($transformed_data['old_id']);
         
            if(isset($project) && $project) {
                $file = 'projects\\' . $project->filename;
                if(Storage::exists($file)) {
                    Storage::delete($file);
                }
                $project->update($transformed_data);
                Log::debug("Updated project: " . $project->id);
            } else {
                $this->_projectRepository->createProject($transformed_data);
                Log::debug("Created project: " . $transformed_data['old_id']);
            }
        } catch (Exception $e ) {
            Log::debug($e->getMessage());
            $webhook->saveException($e);
            return 0;
        }

        return 1;
    }

    public function transform($data) 
    {
        $photo_url = $this->extractValue($data->photo);

        if(isset($photo_url)) {
            $orig_filename = basename($photo_url);
            $file_info = pathinfo($orig_filename);
            $ext = $file_info['extension'];
            $filename = $file_info['filename'];
            $timestamp  = time();
            $newfilename =  $filename. '_' . $timestamp  . "." .  $ext ;
            $path = 'projects\\' . $newfilename;
            $encoded_url = $this->encoded($photo_url);
            Storage::put($path, $this->image_get_contents($encoded_url));
        }

        return [
            'old_id' => $data->id,
            'name' => $data->name,
            'description' => $this->extractValue($data->body) ?? null,
            'summary' => $this->extractValue($data->body_summary) ?? null,
            'filename' => $newfilename ?? null,
            'original_photo_url' => $photo_url ?? null,
            'status' => $data->status ?? null,
        ];
    }
}