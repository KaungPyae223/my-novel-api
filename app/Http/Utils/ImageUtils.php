<?php

namespace App\Http\Utils;

use Cloudinary\Uploader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageUtils
{
    public static function uploadImage($imageOrPath)
    {
        if (!$imageOrPath) {
            return null;
        }

        // Check if it's an instance of UploadedFile (i.e., a file from a request)
        if ($imageOrPath instanceof \Illuminate\Http\UploadedFile) {
            if (!$imageOrPath->isValid()) {
                return null;
            }
            $uploadedImage = cloudinary()->uploadApi()->upload($imageOrPath->getRealPath());
        } elseif (is_string($imageOrPath)) {
            $fullSystemPath = Storage::disk('public')->path($imageOrPath);
            if (!file_exists($fullSystemPath)) {
                return null;
            }
            $uploadedImage = cloudinary()->uploadApi()->upload($fullSystemPath);
        } else {
            return null;
        }

        return [
            'imageUrl' => $uploadedImage['secure_url'],
            'publicId' => $uploadedImage['public_id'],
        ];
    }

    public static function deleteImage($publicId)
    {
        cloudinary()->uploadApi()->destroy($publicId);
    }
}
