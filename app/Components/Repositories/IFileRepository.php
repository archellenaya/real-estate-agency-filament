<?php

namespace App\Components\Repositories;

interface IFileRepository
{
    public function hasFailedImageStatus(): bool;
}
