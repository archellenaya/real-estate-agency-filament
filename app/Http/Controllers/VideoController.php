<?php

namespace App\Http\Controllers;

use App\Exceptions\ProcessException;
use App\Components\Services\IVideoService;

class VideoController extends BaseController
{
    private $_videoService;
    public function __construct(IVideoService $videoService)
    {
        $this->_videoService = $videoService;
    }

    public function getPropertyVideoRedirection($filename)
    {
        try {
            return redirect($this->_videoService->getVideoSrc($filename));
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
    }
}
