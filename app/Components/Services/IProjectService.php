<?php

namespace App\Components\Services;

interface IProjectService 
{
    public function getAllProjects($limit = 10);

    public function getProjectByName($project_name);

    public function getProjectByOldID($old_id);

    public function getProjectByID($id);

    public function getProjectImage($id, $width = null, $height = null);
}