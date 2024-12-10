<?php

namespace App\Http\Controllers;
/**
 * API6
 */
use App\Exceptions\ProcessException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


class BaseController extends Controller
{
    public function setValidationErrorJsonResponse($errors, $code = 422)
    {
        return response()->json(['errors' => $errors], $code);
    }

    public function setJsonMessageResponse($message, $code = 200)
    {
        return response()->json(['message' => $message], $code);
    }

    public function setJsonDataResponse($data = null, $code = 200)
    {
        $data_array = ['data' => $data];

        return response()->json($data_array, $code);
    }

    public function setImageResponse($image, $code=200, $content_type = 'image/jpeg')
    {
        return (new Response($image, $code))->header('Content-Type', $content_type);
    }
}
