<?php

namespace App\Services;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    public function uploadImage($path, $newImage,$currentImage = null)
    {
        if($currentImage){
            Storage::disk('public')->delete($currentImage);
        }
        $imageName = Str::random(20) . uniqid()  . '.webp';
            Image::make($newImage)->encode('webp', 65)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                })->save( Storage::disk('public')->path($path.'/'.$imageName));

        return $path.'/'.$imageName;
    }
}
