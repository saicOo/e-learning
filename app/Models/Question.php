<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    protected $appends = ['image_url','video_url'];

    protected $hidden = [
        'image','video'
    ];

    public function getImageUrlAttribute(){
        if($this->image){
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }
    public function getVideoUrlAttribute(){
        if ($this->video) {
            if ($this->video_type == 'file') {
                return Storage::disk('public')->url($this->video);
            } else {
                return $this->video;
            }
        }
        return null;
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
