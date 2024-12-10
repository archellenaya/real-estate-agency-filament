<?php

namespace App\Components\Services\Impl;


use App\Components\Services\IImageService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Response;
use App\File;
use App\Models\Consultant;
use function PHPUnit\Framework\throwException;
use App\Components\Passive\Utilities;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FacadesFile;

class ImageService implements IImageService
{

    private const extensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tif', 'tiff', 'webp'];

    private const type = [
        'watermark',
        'thumbnail',
        'whitelabel',
        'webfull',
        'watermark_2',
        'thumbnail_2',
        'thumbnail_rebrand',
        'whitelabel_2',
        'webfull_2',
        'webfull_rebrand'
    ];

    const WATERMARK = 'watermark';
    const THUMBNAIL = 'thumbnail';
    const WHITELABEL = 'whitelabel';
    const WEBFULL = 'webfull';
    const WATERMARK_2 = 'watermark_2';
    const THUMBNAIL_2 = 'thumbnail_2';
    const THUMBNAIL_REBRAND = 'thumbnail_rebrand';
    const WHITELABEL_2 = 'whitelabel_2';
    const WEBFULL_2 = 'webfull_2';
    const WEBFULL_REBRAND = 'webfull_rebrand';


    public function getConsultant($filename, $width = null, $height = null, $trials = 1)
    {
        if ($trials > 3) {
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_UNABLE_TO_RETRIVE,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $file_info = pathinfo($filename);

        if (!$this->isFileExtensionSupported($file_info['extension'])) {
            Log::debug($file_info);
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_NOT_SUPPORTED,
                StatusCode::HTTP_BAD_REQUEST
            );
        }
        if (Storage::exists('consultants\\' . $filename)) {
            try {

                $file = Storage::get('consultants\\' . $filename);
                // $image = Image::cache(function ($image)  use ($file, $width, $height) {
                //sometimes images are missed , we attempt to download them 3 times before returning an error

                $image = Image::make($file);
                if (empty($width) === false || empty($height) === false) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                // return  $img;
                // }, 10, true);

                $extension = $file_info['extension'];
            } catch (Exception $e) {
                Log::debug($e->getMessage());
                $newFile = $this->re_upload_consultant($this->getFileModel($filename), $trials);

                if (isset($newFile)) {
                    $filename = $newFile->file_name_field;
                    return $this->getConsultant($filename, $width, $height, ++$trials);
                } else {
                    $default = $this->getDefaultImage("consultant", $width, $height);
                    $image = $default['image'];
                    $extension = $default['extension'];
                }
            }
        } else {
            Log::debug(ProcessExceptionMessage::IMAGE_NOT_FOUND);
            $newFile = $this->re_upload_consultant($this->getConsultantModel($filename), $trials);
            if (isset($newFile)) {
                $filename = $newFile->image_file_name_field;
                return $this->getConsultant($filename, $width, $height, ++$trials);
            } else {
                $default = $this->getDefaultImage("consultant", $width, $height);
                $image = $default['image'];
                $extension = $default['extension'];
            }
        }

        return [
            'image' => $image->encode($extension),
            'code' => StatusCode::HTTP_OK,
            'content_type' => $image->mime
        ];
    }

    private function getConsultantModel($filename)
    {
        $consultant = Consultant::where("image_file_name_field", $filename)->first();
        return $consultant ?? null;
    }

    public function re_upload_consultant($consultant, $trials)
    {
        if (!isset($consultant)) {
            Log::debug("consultant is null");
            return null;
        }

        try {
            // Log::debug("re_upload_file trial " . $trials);

            $orig_path = isset($consultant->orig_consultant_image_src) ? Utilities::stripUrlQueryString($consultant->orig_consultant_image_src) : null;

            if (!isset($orig_path)) {
                return null;
            }

            // Log::debug(  $orig_path );
            $filename = basename($orig_path);
            // Log::debug("filename orig " . $filename);
            $orig =  $filename;

            $file_info = pathinfo($filename);
            $ext = $file_info['extension'];

            $filename = tenant('id') . "-" .  Utilities::slugify($consultant->full_name_field) . "." . $ext;

            $path = 'consultants\\' . $filename;
            $encoded_url = Utilities::encoded($orig_path);
            // Log::debug("encoded_url " . $encoded_url);
            Storage::put($path, Utilities::image_get_contents($encoded_url));

            $consultant->orig_consultant_image_src = $orig_path;
            $consultant->image_file_name_field =  $filename;
            $consultant->image_name_field =  $orig;
            $consultant->updated_at = Carbon::now();


            $consultant->save();

            return $consultant;
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    private function isFileExtensionSupported($file_info)
    {
        if (!isset($file_info) && !in_array(strtolower($file_info),  ImageService::extensions)) {
            return false;
        } else {
            return true;
        }
    }

    public function getProperty($filename, $type = '', $width = null, $height = null, $trials = 1)
    {
        if ($trials > 3) {
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_UNABLE_TO_RETRIVE,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        $file_info = pathinfo($filename);

        if (!$this->isFileExtensionSupported($file_info['extension'])) {
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_NOT_SUPPORTED,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        if (!in_array($type, ImageService::type)) {
            throw new ProcessException(
                ProcessExceptionMessage::TYPE_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }


        if (Storage::exists('property-images\\' . $filename)) {
            $file = Storage::get('property-images\\' . $filename);
            try {
                // $image = Image::cache(function($image) use ($file, $type, $width, $height) {
                $image = Image::make($file);

                $full_size = [
                    self::WEBFULL,
                    self::WEBFULL_2,
                    self::WEBFULL_REBRAND,
                    self::WHITELABEL,
                    self::WHITELABEL_2
                ];

                if ($type === self::THUMBNAIL || $type == self::THUMBNAIL_2 || $type == self::THUMBNAIL_REBRAND) {
                    // $watermark_path = Storage::get('public\\watermark.png');
                    // $watermark = Image::make( $watermark_path );
                    // $image_width = Image::make($file)->width();
                    // $image_height = Image::make($file)->height();
                    // $x_offset = $image_width * 0.15;
                    // $y_offset = $image_height * 0.15;

                    // $image->insert( $watermark, 'top-left', (int) $x_offset, (int) $y_offset);

                    if (!isset($width) && !isset($height)) {
                        $image->resize(380, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                } else if (in_array($type, $full_size)) {
                    // $watermark_middle_path = Storage::get('public\\watermark_middle.png');
                    // $watermark_middle = Image::make( $watermark_middle_path );

                    // $watermark_bottom_left_path = Storage::get('public\\watermark_bottom_left.png');
                    // Log::debug(storage_path('public\\watermark_bottom_left.png'));
                    // $watermark_bottom_left = Image::make( $watermark_bottom_left_path );
                    // $width_watermark_bottom_left = Image::make($file)->width(); //Image::make( $watermark_bottom_left_path )->width();
                    // $height_watermark_bottom_left =  Image::make($file)->height(); //Image::make( $watermark_bottom_left_path )->height();
                    // $height_watermark_bottom_left_20_perc = $height_watermark_bottom_left * 0.20;
                    // $width_watermark_bottom_left_20_perc = $width_watermark_bottom_left * 0.20;

                    // if($width_watermark_bottom_left_20_perc < $height_watermark_bottom_left_20_perc) { //if image in horiZontal swap value
                    //     $temp = $height_watermark_bottom_left_20_perc;
                    //     $height_watermark_bottom_left_20_perc = $width_watermark_bottom_left_20_perc;
                    //     $width_watermark_bottom_left_20_perc = $temp;
                    // }

                    // $watermark_bottom_left->resize( $width_watermark_bottom_left_20_perc, $height_watermark_bottom_left_20_perc, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // });

                    // $image->insert( $watermark_middle, 'center');
                    // $image->insert( $watermark_bottom_left, 'bottom-left');

                    if (!isset($width) && !isset($height)) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                }

                if (isset($width) || isset($height)) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                // }, 10, true);
                $extension = $file_info['extension'];
            } catch (Exception $e) {
                Log::debug($e->getMessage());
                $newFile = $this->re_upload_file($this->getFileModel($filename), $trials);

                if (isset($newFile)) {
                    $filename = $newFile->file_name_field;
                    return $this->getProperty($filename, $type, $width, $height, ++$trials);
                } else {
                    $default = $this->getDefaultImage("property", $width, $height);
                    $image = $default['image'];
                    $extension = $default['extension'];
                }
            }
        } else {
            Log::debug(ProcessExceptionMessage::IMAGE_NOT_FOUND);
            $newFile = $this->re_upload_file($this->getFileModel($filename), $trials);
            if (isset($newFile)) {
                $filename = $newFile->file_name_field;
                return $this->getProperty($filename, $type, $width, $height, ++$trials);
            } else {
                $default = $this->getDefaultImage("property", $width, $height);
                $image = $default['image'];
                $extension = $default['extension'];
            }
        }

        return [
            'image' => $image->encode($extension),
            'code' => StatusCode::HTTP_OK,
            'content_type' => $image->mime
        ];
    }

    private function getFileModel($filename)
    {
        $file = File::where(DB::raw('BINARY `file_name_field`'), $filename)->first();
        return $file ?? null;
    }

    private function re_upload_file($file, $trials)
    {
        if (!isset($file)) {
            return null;
        }

        try {
            Log::debug("re_upload_file trial " . $trials);

            if (!isset($file->orig_image_src)) {
                return null;
            }
            $orig_image_src = $file->orig_image_src;

            $filename = basename(parse_url($orig_image_src, PHP_URL_PATH));

            $orig =  $filename;

            $file_info = parse_url($orig_image_src, PHP_URL_QUERY) ?
                pathinfo($file["original_file_name"]) :
                pathinfo($filename);

            $ext = $file_info['extension'];
            $filename = $file->sequence_no_field . "_" . $file->property_id . "." . $ext;

            $path = 'property-images\\' . $filename;
            $encoded_url = Utilities::encoded($orig_image_src);

            $image_contents = parse_url($orig_image_src, PHP_URL_QUERY) ?
                Utilities::image_get_contents($orig_image_src) :
                Utilities::image_get_contents($encoded_url);

            if (strpos($image_contents, 'Expires') !== false) {
                Log::debug("Failed to fetch image contents from: " . (parse_url($orig_image_src, PHP_URL_QUERY)));
            } else {
                Storage::put($path, $image_contents);
            }

            $file->orig_image_src = $orig_image_src;
            $file->file_name_field =  $filename;
            $file->original_file_name =  $orig;
            $file->mime =  Utilities::mime_content_type($filename);
            $file->updated_at = Carbon::now();

            $file->save();

            return $file;
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }


    private function getDefaultImage($type, $width, $height)
    {
        // Determine the default URL based on the type
        $defaultUrl = $type == "consultant"
            ? Utilities::stripUrlQueryString(config("url.consultant_thumbnail"))
            : Utilities::stripUrlQueryString(config("url.property_thumbnail"));

        // Return null if the default URL is empty
        if (empty($defaultUrl)) {
            return null;
        }

        $pathInfo = pathinfo($defaultUrl);
        $basename = $pathInfo['basename'];
        $extension = $pathInfo['extension'];
        $filePath = public_path(tenant('id') . "/image/" . $type . "/" . $basename);

        // Check if the default image file exists
        if (!FacadesFile::exists($filePath)) {
            Utilities::message("Default image not found");
            return null;
        }

        try {
            // Get the file and create an image instance
            $file = FacadesFile::get($filePath);
            $image = Image::make($file);

            // Resize the image if width or height is provided
            if (!empty($width) || !empty($height)) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            return [
                'image' => $image,
                'extension' => $extension,
            ];
        } catch (\Exception $e) {
            Log::error("Error processing default image: " . $e->getMessage());
            return null;
        }
    }

    private function getImage($filename, $width, $height)
    {

        // $image = Image::cache(function ($image)  use ($file, $width, $height) {
        //sometimes images are missed , we attempt to download them 3 times before returning an error

        if (Storage::exists('public\\' . $filename)) {
            $file = Storage::get("public\\$filename");
            try {
                $image =  Image::make($file);
                if (empty($width) === false || empty($height) === false) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            } catch (\Exception $e) {
            }
            return $image ?? null;
        } else {
            return null;
        }
    }


    public function getPublicImage($filename, $width, $height)
    {
        $file_info = pathinfo($filename);
        $image =  $this->getImage($filename, $width, $height);

        if (isset($image)) {
            return [
                'image' => $image->encode($file_info['extension']),
                'code' => StatusCode::HTTP_OK,
                'content_type' => $image->mime
            ];
        }

        throw new ProcessException(
            ProcessExceptionMessage::IMAGE_NOT_FOUND,
            StatusCode::HTTP_NOT_FOUND
        );
    }

    public function getUser($filename, $width = null, $height = null)
    {

        $file_info = pathinfo($filename);

        if (!$this->isFileExtensionSupported($file_info['extension'])) {
            Log::debug('Unsupported file extension:', $file_info);
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_NOT_SUPPORTED,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        $path = 'users/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_NOT_FOUND,
                StatusCode::HTTP_NOT_FOUND
            );
        }

        try {

            $file = Storage::disk('public')->get($path);
            $image = Image::make($file);

            if ($width || $height) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $extension = $file_info['extension'];
            $encoded_image = $image->encode($extension);
            $mime_type = $image->mime;


            return [
                'image' =>  $encoded_image,
                'code' => StatusCode::HTTP_OK,
                'content_type' => $mime_type
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process image:', ['filename' => $filename, 'error' => $e->getMessage()]);
            throw new ProcessException(
                ProcessExceptionMessage::IMAGE_FILE_UNABLE_TO_RETRIVE,
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
