<?php

namespace App\Http\Utils;

use Cloudinary\Uploader;

class ImageUtils
{
    public static function uploadImage($image)
    {
        if (!$image || !$image->isValid()) {
            return null;
        }

        $uploadedImage = cloudinary()->uploadApi()->upload($image->getRealPath());

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
