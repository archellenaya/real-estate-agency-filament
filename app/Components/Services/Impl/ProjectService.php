<?php

namespace App\Components\Services\Impl;

use App\Components\Services\IProjectService;
use Illuminate\Support\Facades\Log;
use App\Components\Repositories\IProjectRepository;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Exceptions\ProcessException;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;


class ProjectService implements IProjectService {
    
    private $_projectRepository;

    public function __construct(IProjectRepository $projectRepository) 
    {
        $this->_projectRepository  = $projectRepository;
    }

    public function getAllProjects($limit = 10)
    {
      return $this->_projectRepository->getProjects($limit);
    }

    public function getProjectByName($project_name)
    {
        return $this->_projectRepository->getProjectByName($project_name);
    }

    public function getProjectByOldID($old_id)
    {
        return $this->_projectRepository->getProjectByOldID($old_id);
    }

    public function getProjectByID($id)
    {
        return $this->_projectRepository->getProjectByID($id);
    }

    public function getProjectImage($id, $width = null, $height = null)
    {
        $project = $this->getProjectByID($id);

        if(isset($project) && $project) {
            if(isset($project->filename) && $project->filename) {

                return $this->getImage($project->filename, $width, $height);

            } else {
                throw new ProcessException(
                    ProcessExceptionMessage::IMAGE_NOT_FOUND,
                    StatusCode::HTTP_NOT_FOUND
                );
            }
        } else {
            throw new ProcessException(
                ProcessExceptionMessage::PROJECT_NOT_FOUND,
                StatusCode::HTTP_NOT_FOUND
            );
        }
    }

    private function getImage($filename, $width = null, $height = null) 
    {
        $filepath   = 'projects\\' . $filename;
       
        if(Storage::exists( $filepath )) {

            $file_info = explode(".", $filename);
            $file = Storage::get( $filepath );

            $image = Image::cache(function($image) use ($file, $width, $height) {
                $image->make($file);
        
                if (isset($width) || isset($height)) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }, 10, true);
            Log::debug($file_info[1]);
            return [
                'image' => $image->encode($file_info[1]),
                'code' => StatusCode::HTTP_OK,
                'content_type' => $image->mime
            ];

        } else {
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_NOT_FOUND,
                StatusCode::HTTP_NOT_FOUND
            );
        }
    }
}