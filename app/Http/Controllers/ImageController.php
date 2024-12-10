<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Exceptions\ProcessException;
use App\Components\Services\IImageService;
use Illuminate\Support\Facades\Log;


class ImageController extends BaseController
{
    private $_imageService;

    public function __construct(IImageService $imageService)
    {
        $this->_imageService = $imageService;
    }

    public function getConsultantImage(Request $request, $filename)
    {
        $width = $request->width ?? null;
        $height = $request->height ?? null;

        try {
            $result = $this->_imageService->getConsultant($filename,  $width, $height);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setImageResponse($result['image'], $result['code'], $result['content_type']);
    }

    public function getPropertyImage(Request $request, $type, $filename)
    {

        $width = $request->width ?? null;
        $height = $request->height ?? null;

        try {
            $result =  $this->_imageService->getProperty($filename, $type, $width, $height);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
        return $this->setImageResponse($result['image']);
    }


    public function getPublicImage(Request $request, $filename)
    {
        $width = $request->width ?? null;
        $height = $request->height ?? null;

        try {

            $result =  $this->_imageService->getPublicImage($filename, $width, $height);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setImageResponse($result['image']);
    }
    public function getUserImage(Request $request, $filename)
    {
        $width = $request->width ?? null;
        $height = $request->height ?? null;

        try {
            $result = $this->_imageService->getUser($filename,  $width, $height);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setImageResponse($result['image'], $result['code'], $result['content_type']);
    }
}
