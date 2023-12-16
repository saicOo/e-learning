<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listen extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['video_url','attached_url'];

    protected $hidden = [
        'video',
        'attached',
    ];

    public function getVideoUrlAttribute(){
        return Storage::disk('public')->url($this->video);
    }
    public function getAttachedUrlAttribute(){
        if($this->attached){
            return Storage::disk('public')->url($this->attached);
        }
        return null;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
