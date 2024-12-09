<?php

namespace App\Components\Repositories\Impl;

use App\Models\File;
use App\Components\Repositories\IFileRepository;

class FileRepository implements IFileRepository
{
    protected $model;

    public function __construct(File $model)
    {
        $this->model = $model;
    }

    public function hasFailedImageStatus(): bool
    {
        return $this->model->where('image_status_field', 'failed')
            ->where(function ($query) {
                $query->where('optimization_retries', '<=', 3)
                    ->orWhereNull('optimization_retries');
            })
            ->exists();
    }
}
