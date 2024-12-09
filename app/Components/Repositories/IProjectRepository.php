<?php

namespace App\Components\Repositories;

interface IProjectRepository
{
    public function createProject($data); 

    public function updateProject($id, $data); 

    public function getProjectByOldID($id); 
    
    public function getProjects($limit);

    public function getProjectByName($project_name);

    public function getProjectByID($id);
}
