<?php

namespace App\Components\Services\Impl;

use App\Models\File;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Components\Services\IVideoService;
use App\Constants\Exception\ProcessExceptionMessage;

class VideoService implements IVideoService
{
    public function getVideoSrc($fileName)
    {
        $file = File::where('file_name_field', $fileName)
            ->where('file_type_field', 'Video')
            ->first();

        if (!$file) {
            throw new ProcessException(
                ProcessExceptionMessage::VIDEO_NOT_FOUND,
                StatusCode::HTTP_NOT_FOUND
            );
        }

        $origImageSrc = $file->orig_image_src;

        return $origImageSrc;
    }
}
