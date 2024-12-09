<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IProjectRepository;
use App\Project;

class ProjectRepository implements IProjectRepository
{
    public function createProject($data)
    {
        return Project::create($data);
    }

    public function updateProject($id, $data)
    {
        return Project::where("id", $id)->update($data);
    }

    public function getProjectByOldID($id)
    {
        return Project::where("old_id", $id)->first();
    } 

    public function getProjects( $limit )
    {
        return Project::where('status', 1)->paginate( $limit );
    }

    public function getProjectByName($project_name)
    {
        return Project::where('status', 1)->where('name', $project_name)->first();
    }

    public function getProjectByID($id)
    {
        return Project::findOrFail($id);
    }
}
