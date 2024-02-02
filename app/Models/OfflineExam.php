<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfflineExam extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'images' => 'array',
    ];
    protected $appends = ['images_url'];
    protected $hidden = [
        'images',
    ];

    public function getImagesUrlAttribute(){
        $images = [];
        if($this->images){
            foreach ($this->images as $image) {
                $image_url = Storage::disk('public')->url($image);
                array_push($images , $image_url);
            }
        }
        return $images;
    }
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
